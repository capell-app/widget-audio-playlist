# Audio Playlist Widget

<!-- prettier-ignore-start -->

## What it does

Audio Playlist lets editors publish podcasts, briefings, guided audio, and other ordered track collections using media already managed in Capell. Visitors get native browser controls, optional artwork, transcripts, and chapters.

## Do I need to do anything?

Add **Audio Playlist** in Layout Builder, give the playlist a title, then add one to twenty ordered tracks. Each track needs a title, artist, and locally uploaded audio file. You can optionally add artwork, a transcript, and up to fifty labelled chapter jump points. Preview the public page before publishing.

## Where it shows up

In Layout Builder, as an Audio Playlist widget with fields for up to 20 ordered tracks. On the public page it renders a compact server-side playlist.

## Good to know

- This free beta extension belongs to Capell Foundation and requires Layout Builder.
- Tracks and artwork must use local media; remote audio URLs are rejected.
- Playback never autostarts, and each audio element uses `preload="none"`.
- Transcripts and chapters are rendered as expandable disclosures under their track. Chapter controls seek within the native audio player.

## Screenshot status

Capture the configured Layout Builder fields and a public playlist with at least two tracks, artwork, and an expanded transcript disclosure. The committed Marketplace card is interim beta artwork until these two fixture captures are recorded.

Widgets added through deferred content or inline reveal initialise when the content arrives. Repeated content-ready events do not duplicate listeners or countdown timers.

---

For developers: see the [README](../README.md).

<!-- prettier-ignore-end -->
