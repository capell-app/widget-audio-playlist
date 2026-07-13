# Audio Playlist Widget

<!-- prettier-ignore-start -->

## What This Plugin Adds

Audio Playlist Widget is an **Available**, **No schema impact** Capell package in the **Capell Foundation** product group. It ships as `capell-app/widget-audio-playlist` and extends these surfaces: admin, frontend.

Up to 20 uploaded tracks with native controls, artwork, transcripts, and chapters.

After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.

Status details:

- Status: Available
- Tier: free
- Bundle: foundation
- Composer package: `capell-app/widget-audio-playlist`
- Namespace: `Capell\WidgetAudioPlaylist`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Data objects, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Accessible native audio playlists with transcripts and chapters.

## Screens And Workflow

Marketplace media is declared in `capell.json`:

- Audio Playlist widget.

## Technical Shape

- Service providers: `Capell\WidgetAudioPlaylist\Providers\WidgetAudioPlaylistServiceProvider`.
- Filament classes: `AudioPlaylistWidget`.
- Data objects: `AudioChapterData`, `AudioPlaylistInputData`, `AudioPlaylistRenderData`, `AudioTrackData`.
- Manifest contributions: `content-widget: Capell\WidgetAudioPlaylist\WidgetAudioPlaylistContribution`.
- Health checks: `Capell\WidgetAudioPlaylist\Health\WidgetAudioPlaylistHealthCheck`.
- Blade views: `packages/widget-audio-playlist/resources/views/widget.blade.php`.
- Cache tags: `widget-audio-playlist`.

## Data Model

This package has no schema impact. It does not declare package-owned migrations or required tables.

Docs gap: document extension points here if the package delegates persistence to a host package.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `widget-audio-playlist`.
- Commands: none declared.

## Common Pitfalls

- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/widget-audio-playlist`.
2. Run the required setup: no package migrations are declared; clear cached config and routes if the host app uses caches.
3. Open the related Capell admin surface and verify Audio Playlist Widget appears.

## Next Steps

- [Package docs index](README.md)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Layout Builder](../../layout-builder/README.md).
- Focused tests: `vendor/bin/pest packages/widget-audio-playlist/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
