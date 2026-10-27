import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { execFileSync } from 'node:child_process'
import { runInNewContext } from 'node:vm'
import { test } from 'node:test'

// Minimal DOM adapter over the actual PHP DOM tree of the shipped Blade.
// Browser interaction and layout are covered separately by widget-lifecycle.
class Element {
    constructor(
        { tag, attributes = {}, text = '', children = [] },
        parent = null,
    ) {
        this.hidden = 'hidden' in attributes
        this.tagName = tag
        this.attributes = attributes
        this.textContent = text
        this.parentElement = parent
        this.children = children.map((child) => new Element(child, this))
        this.listeners = {}
        this.isConnected = true
        this.dataset = Object.fromEntries(
            Object.entries(attributes)
                .filter(([key]) => key.startsWith('data-'))
                .map(([key, value]) => [
                    key
                        .slice(5)
                        .replace(/-([a-z])/g, (_, letter) =>
                            letter.toUpperCase(),
                        ),
                    value,
                ]),
        )
    }
    matches(selector) {
        return selector.startsWith('.')
            ? (this.attributes.class ?? '')
                  .split(' ')
                  .includes(selector.slice(1))
            : selector.startsWith('[')
              ? selector.slice(1, -1) in this.attributes
              : selector === this.tagName
    }
    querySelectorAll(selector) {
        return this.children.flatMap((child) => [
            ...(child.matches(selector) ? [child] : []),
            ...child.querySelectorAll(selector),
        ])
    }
    querySelector(selector) {
        return this.querySelectorAll(selector)[0] ?? null
    }
    closest(selector) {
        return this.matches(selector)
            ? this
            : this.parentElement?.closest(selector)
    }
    addEventListener(name, listener) {
        ;(this.listeners[name] ??= []).push(listener)
    }
    dispatchEvent(event) {
        event.target ??= this
        event.currentTarget = this
        for (const listener of this.listeners[event.type] ?? []) listener(event)
        if (event.bubbles) this.parentElement?.dispatchEvent(event)
        return true
    }
    setAttribute(name, value) {
        this.attributes[name] = value
    }
    replaceChildren(...children) {
        this.children = children
    }
    pause() {
        this.pauses = (this.pauses ?? 0) + 1
    }
    play() {
        this.plays = (this.plays ?? 0) + 1
        this.dispatchEvent({ type: 'play' })
        return this.reject
            ? Promise.reject(new Error('Denied'))
            : Promise.resolve()
    }
}
function fixture(slug, options = {}) {
    return new Element(
        JSON.parse(
            execFileSync(
                process.env.PHP_BINARY ?? 'php',
                [
                    'packages/widget-audio-playlist/tests/Support/render-public-widgets.php',
                    `widget-${slug}`,
                    'tree',
                ],
                { input: JSON.stringify(options), encoding: 'utf8' },
            ),
        ),
    )
}
function runtime(slug, document = fixture(slug), reduced = false) {
    const timers = new Map()
    let next = 0
    let now = Date.parse('2030-01-01T00:00:00Z')
    const window = {
        matchMedia: () => ({ matches: reduced }),
        setInterval: (callback) => {
            timers.set(++next, callback)
            return next
        },
        clearInterval: (timer) => timers.delete(timer),
        CapellPrivacy: { allows: () => false },
    }
    document.createElement = (tag) => new Element({ tag })
    runInNewContext(
        readFileSync(
            `${process.env.PK_WIDGET_SOURCE_ROOT ?? '.'}/packages/widget-${slug}/resources/dist/widget.js`,
            'utf8',
        ),
        {
            document,
            window,
            Element,
            Date: class extends Date {
                static now() {
                    return now
                }
            },
            MutationObserver: class {
                observe() {}
            },
            console,
        },
    )
    return {
        document,
        window,
        timers,
        tick: () => {
            now += 1000
            for (const callback of [...timers.values()]) callback()
        },
    }
}

test('chapter controls execute the owning audio seek and announce a rejected play', async () => {
    const { document } = runtime('audio-playlist')
    const players = document.querySelectorAll('audio')
    const chapters = document.querySelectorAll('[data-audio-seek]')
    chapters[1].dispatchEvent({ type: 'click' })
    assert.equal(players[0].currentTime, 35)
    assert.equal(players[0].plays, 1)
    assert.equal(players[1].plays, undefined)
    players[0].reject = true
    chapters[0].dispatchEvent({ type: 'click' })
    await new Promise((resolve) => setImmediate(resolve))
    assert.ok(document.querySelector('[data-audio-status]').textContent.trim())
})

for (const slug of ['audio-playlist', 'countdown', 'location-map'])
    test(`${slug} initialises dynamically inserted roots exactly once`, async () => {
        const state = runtime(slug)
        const inserted = fixture(slug).querySelector(
            `[data-capell-widget-${slug}]`,
        )
        inserted.parentElement = state.document
        state.document.children.push(inserted)
        inserted.dispatchEvent({ type: 'capell:content-ready', bubbles: true })
        inserted.dispatchEvent({ type: 'capell:content-ready', bubbles: true })
        if (slug === 'audio-playlist') {
            const players = inserted.querySelectorAll('audio')
            await players[0].play()
            assert.equal(players[1].pauses, 1)
        } else if (slug === 'countdown') {
            assert.equal(state.timers.size, 2)
            state.tick()
            assert.equal(
                inserted.querySelector('[data-seconds]').textContent,
                '59',
            )
            inserted.isConnected = false
            state.tick()
            assert.equal(state.timers.size, 1)
            inserted.isConnected = true
            inserted.dispatchEvent({
                type: 'capell:content-ready',
                bubbles: true,
            })
            assert.equal(state.timers.size, 2)
        } else {
            inserted
                .querySelector('[data-map-activate]')
                .dispatchEvent({ type: 'click' })
            assert.ok(
                inserted.querySelector('[data-map-status]').textContent.trim(),
            )
            assert.equal(inserted.querySelector('iframe'), null)
            state.window.CapellPrivacy.allows = () => true
            inserted
                .querySelector('[data-map-activate]')
                .dispatchEvent({ type: 'click' })
            assert.equal(inserted.querySelectorAll('iframe').length, 1)
            assert.equal(
                inserted.querySelector('[data-map-activate]').listeners.click
                    .length,
                1,
            )
        }
    })

for (const controls of [false, true])
    for (const auto of [false, true])
        for (const reduced of [false, true])
            test(`slideshow manual reachability controls=${controls} auto=${auto} reduced=${reduced}`, () => {
                const { document, timers } = runtime(
                    'slideshow',
                    fixture('slideshow', { controls, auto }),
                    reduced,
                )
                const slides = document.querySelectorAll(
                    '.capell-slideshow__slide',
                )
                document
                    .querySelector('[data-slideshow-next]')
                    .dispatchEvent({ type: 'click' })
                assert.equal(slides[0].hidden, true)
                assert.equal(slides[1].hidden, false)
                document
                    .querySelector('[data-slideshow-previous]')
                    .dispatchEvent({ type: 'click' })
                assert.equal(slides[0].hidden, false)
                assert.equal(slides[1].hidden, true)
                assert.equal(timers.size, auto && !reduced ? 1 : 0)
            })

for (const consent of [false, true])
    test(`map keeps previous public markup accessible with consent=${consent}`, () => {
        const document = fixture('location-map')
        const root = document.querySelector('[data-capell-widget-location-map]')
        for (const key of ['deniedLabel', 'frameTitle'])
            delete root.dataset[key]
        for (const key of ['data-denied-label', 'data-frame-title'])
            delete root.attributes[key]
        const { window } = runtime('location-map', document)
        window.CapellPrivacy.allows = () => consent
        root.querySelector('[data-map-activate]').dispatchEvent({
            type: 'click',
        })
        if (consent) {
            const frame = root.querySelector('iframe')
            assert.equal(typeof frame.title, 'string')
            assert.ok(frame.title.trim())
        } else {
            const status = root.querySelector('[data-map-status]')
            assert.equal(typeof status.textContent, 'string')
            assert.ok(status.textContent.trim())
            assert.equal(root.querySelector('iframe'), null)
        }
    })

test('countdown mounts previous public markup and still registers dynamic content and expires', () => {
    const document = fixture('countdown')
    const root = document.querySelector('[data-capell-widget-countdown]')
    delete root.dataset.remainingLabel
    delete root.attributes['data-remaining-label']
    const state = runtime('countdown', document)
    assert.equal(state.timers.size, 1)
    assert.equal(document.listeners['capell:content-ready'].length, 1)
    assert.equal(root.querySelector('[data-seconds]').textContent, '0')
    assert.ok(root.querySelector('[data-countdown-status]').textContent.trim())
    const inserted = fixture('countdown').querySelector(
        '[data-capell-widget-countdown]',
    )
    inserted.parentElement = document
    document.children.push(inserted)
    inserted.dispatchEvent({ type: 'capell:content-ready', bubbles: true })
    assert.equal(state.timers.size, 2)
    state.tick()
    assert.equal(root.querySelector('[data-seconds]').textContent, '59')
    for (let second = 1; second < 60; second++) state.tick()
    assert.equal(
        root.querySelector('[data-countdown-status]').textContent,
        root.dataset.expiredLabel,
    )
    assert.equal(state.timers.size, 0)
})

for (const controls of [false, true])
    for (const reduced of [false, true])
        test(`slideshow advertises its actual playback action controls=${controls} reduced=${reduced}`, () => {
            const { document, timers, tick } = runtime(
                'slideshow',
                fixture('slideshow', { controls, auto: true }),
                reduced,
            )
            const toggle = document.querySelector('[data-slideshow-toggle]')
            assert.equal(
                toggle.textContent.trim(),
                reduced
                    ? toggle.dataset.labelResume
                    : toggle.dataset.labelPause,
            )
            assert.equal(
                toggle.attributes['aria-label'],
                reduced
                    ? toggle.dataset.labelResumeSlideshow
                    : toggle.dataset.labelPauseSlideshow,
            )
            assert.equal(toggle.attributes['aria-pressed'], String(reduced))
            assert.equal(timers.size, reduced ? 0 : 1)
            toggle.dispatchEvent({ type: 'click' })
            assert.equal(
                toggle.textContent,
                reduced
                    ? toggle.dataset.labelPause
                    : toggle.dataset.labelResume,
            )
            assert.equal(
                toggle.attributes['aria-label'],
                reduced
                    ? toggle.dataset.labelPauseSlideshow
                    : toggle.dataset.labelResumeSlideshow,
            )
            assert.equal(toggle.attributes['aria-pressed'], String(!reduced))
            assert.equal(timers.size, reduced ? 1 : 0)
            tick()
            assert.equal(
                document.querySelectorAll('.capell-slideshow__slide')[1].hidden,
                !reduced,
            )
        })
