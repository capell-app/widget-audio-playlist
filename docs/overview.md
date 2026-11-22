# Audio Playlist Widget

<!-- prettier-ignore-start -->

Recordings of your talks, sermons or podcast episodes go on your own page as an audio playlist. Listeners see them listed in the order you choose and play each one right there in the browser.

The files have to be ones you upload, which means a Spotify or SoundCloud link won't work here. Each track gets a title and an artist, plus a picture if you want one. Paste in a written version of a talk and listeners can open it under a Transcript heading.

Chapters let people skip to the part they came for. Give each part of a recording a name and the time it starts, and it shows as a button such as "Sermon (08:40)". Tapping it jumps the track to that point and plays from there.

No audio downloads until someone presses play, which keeps the page quick to open. Tracks don't run on from one to the next, and starting a second track pauses the first.

## What it does

Audio Playlist lets editors publish podcasts, briefings, guided audio, and other ordered track collections using media already managed in Capell. Visitors get native browser controls, optional artwork, transcripts, and chapters.

## Do I need to do anything?

Add **Audio Playlist** in Layout Builder, give the playlist a title, then add one to twenty ordered tracks. Each track needs a title, artist, and locally uploaded audio file. You can optionally add artwork, a transcript, and up to fifty labelled chapter jump points. Preview the public page before publishing.

## Where it shows up

In Layout Builder, as an Audio Playlist widget with fields for up to 20 ordered tracks. On the public page it renders a compact server-side playlist.

## Good to know

- This free extension belongs to Capell Essentials and requires Layout Builder.
- Tracks and artwork must use local media; remote audio URLs are rejected.
- Playback never autostarts, and each audio element uses `preload="none"`.
- Transcripts and chapters are rendered as expandable disclosures under their track. Chapter controls seek within the native audio player.

Widgets added through deferred content or inline reveal initialise when the content arrives. Repeated content-ready events do not duplicate listeners or countdown timers.

---

For developers: see the [README](../README.md).

<!-- prettier-ignore-end -->
