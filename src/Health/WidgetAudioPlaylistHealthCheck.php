<?php

declare(strict_types=1);

namespace Capell\WidgetAudioPlaylist\Health;

use Capell\LayoutBuilder\Health\AbstractWidgetExtensionHealthCheck;
use Capell\WidgetAudioPlaylist\Filament\AudioPlaylistWidget;

final class WidgetAudioPlaylistHealthCheck extends AbstractWidgetExtensionHealthCheck
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^0.0';
    }

    protected static function definitionKey(): string
    {
        return 'capell-app.audio-playlist';
    }

    protected static function filamentWidget(): string
    {
        return AudioPlaylistWidget::class;
    }

    protected static function fallbackView(): string
    {
        return 'capell-widget-audio-playlist::widget';
    }

    protected static function resourceGroups(): array
    {
        return ['capell-app.widget-audio-playlist'];
    }
}
