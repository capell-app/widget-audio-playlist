;(() => {
    const mounted = new WeakSet()
    const initialise = (playlist) => {
        if (mounted.has(playlist)) return
        mounted.add(playlist)
        const players = Array.from(playlist.querySelectorAll('audio'))
        players.forEach((player) =>
            player.addEventListener('play', () => {
                players
                    .filter((candidate) => candidate !== player)
                    .forEach((candidate) => candidate.pause())
            }),
        )
        playlist.querySelectorAll('[data-audio-seek]').forEach((button) =>
            button.addEventListener('click', () => {
                const track = button.closest('[data-audio-track]')
                const player = track?.querySelector('audio')
                const status = track?.querySelector('[data-audio-status]')
                if (!player) return
                if (status) status.textContent = ''
                const failed = () => {
                    if (status)
                        status.textContent = playlist.dataset.playbackError
                }
                try {
                    player.currentTime = Number(button.dataset.audioSeek)
                    player.play().catch(failed)
                } catch {
                    failed()
                }
            }),
        )
    }
    const mount = (root) => {
        if (root.matches?.('[data-capell-widget-audio-playlist]'))
            initialise(root)
        root.querySelectorAll?.('[data-capell-widget-audio-playlist]').forEach(
            initialise,
        )
    }
    mount(document)
    document.addEventListener('capell:content-ready', (event) =>
        mount(event.target),
    )
})()
