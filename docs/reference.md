# Audio Playlist Widget reference

<!-- prettier-ignore-start -->

Developer reference for `capell-app/widget-audio-playlist`. Start with the [README](../README.md) for what the extension does and how to install it.

## Package Details

Audio Playlist Widget is an **Available**, **No schema impact** Capell package in the **Capell Essentials** product group. It ships as `capell-app/widget-audio-playlist` and extends these surfaces: admin, frontend.

- Status: Available
- Tier: free
- Bundle: foundation
- Composer package: `capell-app/widget-audio-playlist`
- Namespace: `Capell\WidgetAudioPlaylist`
- Theme key: not applicable

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

<!-- prettier-ignore-end -->
