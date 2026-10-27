import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { execFileSync } from 'node:child_process'
import { after, before, test } from 'node:test'
import { chromium } from 'playwright'
import { build } from 'esbuild'

let browser
const render = (slug, options = {}) =>
    execFileSync(
        process.env.PHP_BINARY ?? 'php',
        [
            'packages/widget-audio-playlist/tests/Support/render-public-widgets.php',
            `widget-${slug}`,
        ],
        { input: JSON.stringify(options), encoding: 'utf8' },
    )
const script = (slug) =>
    readFileSync(`packages/widget-${slug}/resources/dist/widget.js`, 'utf8')
before(async () => {
    browser = await chromium.launch()
})
after(async () => browser?.close())
const open = async (html) => {
    const page = await browser.newPage()
    await page.route('**/*', (route) => route.abort())
    await page.setContent(html)
    await page.evaluate(() => {
        HTMLMediaElement.prototype.play = function () {
            this.dataset.played = 'true'
            this.dispatchEvent(new Event('play'))
            return this.dataset.reject
                ? Promise.reject(new Error('Denied'))
                : Promise.resolve()
        }
        HTMLMediaElement.prototype.pause = function () {
            this.dataset.paused = 'true'
        }
    })
    return page
}

test('nested chapters seek and play only their owning track; rejected playback is announced', async () => {
    const page = await open(render('audio-playlist'))
    try {
        await page.addScriptTag({ content: script('audio-playlist') })
        await page.locator('details').first().locator('summary').click()
        await page.locator('[data-audio-seek]').nth(1).focus()
        await page.keyboard.press('Enter')
        assert.equal(
            await page
                .locator('audio')
                .nth(0)
                .evaluate((el) => el.currentTime),
            35,
        )
        assert.equal(
            await page.locator('audio').nth(1).getAttribute('data-played'),
            null,
        )
        await page
            .locator('audio')
            .first()
            .evaluate((el) => (el.dataset.reject = 'true'))
        await page.locator('[data-audio-seek]').first().click()
        assert.ok(
            (
                await page.locator('[data-audio-status]').first().textContent()
            ).trim(),
        )
    } finally {
        await page.close()
    }
})

for (const controls of [false, true])
    for (const auto of [false, true])
        for (const reducedMotion of ['reduce', 'no-preference']) {
            test(`slideshow reaches CTA with controls=${controls} auto=${auto} motion=${reducedMotion}`, async () => {
                const page = await open(render('slideshow', { controls, auto }))
                try {
                    await page.emulateMedia({ reducedMotion })
                    await page.addScriptTag({ content: script('slideshow') })
                    if (auto) {
                        const toggle = page.locator('[data-slideshow-toggle]')
                        const paused = reducedMotion === 'reduce'
                        const action = paused ? 'resume' : 'pause'
                        assert.equal(
                            (await toggle.textContent()).trim(),
                            await toggle.getAttribute(`data-label-${action}`),
                        )
                        assert.equal(
                            await toggle.getAttribute('aria-label'),
                            await toggle.getAttribute(
                                `data-label-${action}-slideshow`,
                            ),
                        )
                        assert.equal(
                            await toggle.getAttribute('aria-pressed'),
                            String(paused),
                        )
                        await toggle.click()
                        assert.equal(
                            await toggle.getAttribute('aria-pressed'),
                            String(!paused),
                        )
                    }
                    const next = page.locator('[data-slideshow-next]')
                    assert.equal(await next.count(), 1)
                    await next.focus()
                    await page.keyboard.press('Enter')
                    assert.equal(
                        await page.locator('a[href="/visit"]').isVisible(),
                        true,
                    )
                    await page.locator('[data-slideshow-previous]').click()
                    assert.equal(
                        await page
                            .locator('.capell-slideshow__slide')
                            .first()
                            .isVisible(),
                        true,
                    )
                } finally {
                    await page.close()
                }
            })
        }

test('real inline reveal initialises loaded audio, countdown and map assets once and respects privacy', async () => {
    const html =
        render('audio-playlist') + render('countdown') + render('location-map')
    const page = await open(
        html +
            '<div class="capell-interactions"><a href="/reveal" data-capell-interaction data-capell-interaction-behavior="inline_reveal">Reveal</a></div>',
    )
    try {
        // A running fake clock keeps advancing in real time, so pause it to
        // make the elapsed seconds depend only on the explicit fast-forward.
        await page.clock.install({ time: new Date('2029-12-31T23:59:59Z') })
        await page.clock.pauseAt(new Date('2030-01-01T00:00:00Z'))
        await page.evaluate((html) => {
            window.fetch = async () => ({
                ok: true,
                json: async () => ({
                    version: 2,
                    status: 'ok',
                    html,
                    resource_ids: [],
                }),
            })
            window.CapellPrivacy = { allows: () => false }
            window.timerCount = 0
            const original = window.setInterval
            window.setInterval = (...args) => {
                window.timerCount++
                return original(...args)
            }
        }, html)
        for (const slug of ['audio-playlist', 'countdown', 'location-map'])
            await page.addScriptTag({ content: script(slug) })
        const runtime = await build({
            entryPoints: [
                'vendor/capell-app/frontend/resources/js/widget-runtime.js',
            ],
            bundle: true,
            write: false,
        })
        await page.addScriptTag({ content: runtime.outputFiles[0].text })
        await page.locator('a[data-capell-interaction]').click()
        const region = page.locator('.capell-interaction-inline')
        await region.waitFor()
        await page.waitForFunction(
            () =>
                document
                    .querySelector('.capell-interaction-inline')
                    ?.getAttribute('aria-busy') === 'false',
        )
        await region
            .locator('audio')
            .first()
            .evaluate((el) => el.play())
        assert.equal(
            await region.locator('audio').nth(1).getAttribute('data-paused'),
            'true',
        )
        await page.clock.fastForward(2000)
        assert.equal(await region.locator('[data-seconds]').textContent(), '58')
        await region.locator('[data-map-activate]').click()
        assert.ok(
            (await region.locator('[data-map-status]').textContent()).trim(),
        )
        assert.equal(await region.locator('iframe').count(), 0)
        await page.evaluate(() => (window.CapellPrivacy.allows = () => true))
        await region.locator('[data-map-activate]').click()
        assert.equal(await region.locator('iframe').count(), 1)
        const timers = await page.evaluate(() => window.timerCount)
        await region.evaluate((el) => {
            el.dispatchEvent(
                new CustomEvent('capell:content-ready', { bubbles: true }),
            )
            el.querySelector('[data-capell-widget-countdown]').dispatchEvent(
                new CustomEvent('capell:content-ready', { bubbles: true }),
            )
        })
        assert.equal(await page.evaluate(() => window.timerCount), timers)
        await region.evaluate((el) => el.remove())
        await page.clock.fastForward(1000)
    } finally {
        await page.close()
    }
})

test('without scripting every slide and its CTA remain visible', async () => {
    const page = await browser.newPage({ javaScriptEnabled: false })
    await page.route('**/*', (route) => route.abort())
    try {
        await page.setContent(
            '<style>' +
                readFileSync(
                    'packages/widget-slideshow/resources/dist/widget.css',
                    'utf8',
                ) +
                '</style>' +
                render('slideshow', { controls: false, auto: false }),
        )
        for (const slide of await page
            .locator('.capell-slideshow__slide')
            .all())
            assert.equal(await slide.isVisible(), true)
        assert.equal(await page.locator('a[href="/visit"]').isVisible(), true)
    } finally {
        await page.close()
    }
})
