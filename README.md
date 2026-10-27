# Audio Playlist Widget

<!-- prettier-ignore-start -->

## What This Plugin Adds

Audio Playlist Widget is an **Available**, **No schema impact** Capell package in the **Capell Foundation** product group. It ships as `capell-app/widget-audio-playlist` and extends these surfaces: admin, frontend.

Audio Playlist adds a Layout Builder widget for local audio tracks with artwork, native controls, transcripts, and chapter markers. Its configuration accepts a bounded playlist and rejects remote audio sources.

Editors configure the playlist in Layout Builder, and visitors receive a public player with track context and supporting transcript or chapter content when supplied.

Status details:

- Status: Available
- Tier: free
- Bundle: foundation
- Composer package: `capell-app/widget-audio-playlist`
- Namespace: `Capell\WidgetAudioPlaylist`
- Theme key: not applicable

## Why It Matters

**For developers:** The package registers a typed widget extension and validates media and nested track data before the public Blade render.

**For teams:** Teams can publish a structured listening experience with useful context instead of assembling separate players and transcript blocks.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

![Audio Playlist public output](docs/screenshots/audio-playlist-public-output.png)

![Audio Playlist fields in Layout Builder](docs/screenshots/audio-playlist-layout-builder-editor.png)

- Audio Playlist Widget extension card (marketplace, supplementary documentation fixture).
- Audio Playlist fields in Layout Builder (admin, supplementary evidence).
- Audio Playlist public output (frontend, required evidence).
- Audio Playlist fields in Layout Builder with admin sidebar menu open (admin, supplementary evidence).

## Technical Shape

### Service providers

- `Capell\WidgetAudioPlaylist\Providers\WidgetAudioPlaylistServiceProvider`

### Filament classes

- `AudioPlaylistWidget`

### Data objects

- `AudioChapterData`
- `AudioPlaylistInputData`
- `AudioPlaylistRenderData`
- `AudioTrackData`

### Manifest contributions

- `asset: Capell\WidgetAudioPlaylist\WidgetAudioPlaylistContribution`
- `content-widget: Capell\WidgetAudioPlaylist\WidgetAudioPlaylistContribution`

### Health checks

- `Capell\WidgetAudioPlaylist\Health\WidgetAudioPlaylistHealthCheck`

### Blade views

- `packages/widget-audio-playlist/resources/views/widget.blade.php`

### Cache tags

- `widget-audio-playlist`


## Data Model

This package has no schema impact. It extends Capell through `asset` contributions and `content-widget` contributions instead of declaring package-owned tables.

## Install Impact

- Required packages: `capell-app/admin`, `capell-app/core`, `capell-app/frontend`, `capell-app/layout-builder`.
- Admin navigation: no admin page or resource contribution is declared.
- Admin/editor extensions: `content-widget: WidgetAudioPlaylistContribution`.
- Permissions: no package permission declarations or Shield gates detected; host access rules still apply.
- Public routes: none declared.
- Database changes: no package migrations declared.
- Config: no package config files.
- Settings: no package settings declared.
- Queues or schedules: none declared.
- Cache tags: `widget-audio-playlist`.
- Commands: none declared.

## Common Pitfalls

- Keep required Capell packages on compatible v4 releases: `capell-app/admin`, `capell-app/core`, `capell-app/frontend`, `capell-app/layout-builder`.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Custom write integrations must preserve invalidation for `widget-audio-playlist` cache tags.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |
| The playlist is refused | It has no tracks or more than 20 | Count the tracks in the widget | Keep between 1 and 20 tracks |
| A track is refused | The audio or artwork path is not a local media path, or a title or artist is over 120 characters | Check the track's audio and artwork fields | Pick files from the media library rather than pasting a web address, and shorten long titles |

## Quick Start

1. Install the package: `composer require capell-app/widget-audio-playlist`.
2. Open a page, click **Layout builder**, **Add widgets**, then choose **Audio Playlist**.

## Upgrading

No manual steps between minor versions.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Troubleshooting](#troubleshooting)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Layout Builder](../layout-builder/README.md).

<!-- prettier-ignore-end -->
