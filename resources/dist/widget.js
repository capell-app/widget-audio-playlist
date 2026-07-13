document
    .querySelectorAll('[data-capell-audio-playlist]')
    .forEach((playlist) => {
        const players = Array.from(playlist.querySelectorAll('audio'))
        players.forEach((player) =>
            player.addEventListener('play', () =>
                players
                    .filter((candidate) => candidate !== player)
                    .forEach((candidate) => candidate.pause()),
            ),
        )
        playlist.querySelectorAll('[data-audio-seek]').forEach((button) =>
            button.addEventListener('click', () => {
                const player = button.closest('li')?.querySelector('audio')
                if (player) {
                    player.currentTime = Number(button.dataset.audioSeek)
                    player.play().catch(() => {})
                }
            }),
        )
    })
