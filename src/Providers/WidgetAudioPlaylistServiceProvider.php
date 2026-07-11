<?php

declare(strict_types=1);

namespace Capell\WidgetAudioPlaylist\Providers;

use Capell\Core\Enums\PresentationLoadingStrategy;
use Capell\LayoutBuilder\Data\WidgetExtensions\WidgetExtensionCapabilitiesData;
use Capell\LayoutBuilder\Data\WidgetExtensions\WidgetExtensionDefinitionData;
use Capell\LayoutBuilder\Enums\WidgetPresentationCapability;
use Capell\LayoutBuilder\Providers\AbstractFoundationWidgetServiceProvider;
use Capell\WidgetAudioPlaylist\Data\AudioPlaylistInputData;
use Capell\WidgetAudioPlaylist\Data\AudioPlaylistRenderData;
use Capell\WidgetAudioPlaylist\Filament\AudioPlaylistWidget;

final class WidgetAudioPlaylistServiceProvider extends AbstractFoundationWidgetServiceProvider
{
    public static string $name = 'capell-widget-audio-playlist';

    public static string $packageName = 'capell-app/widget-audio-playlist';

    protected static function resourceGroup(): string
    {
        return 'capell-app.widget-audio-playlist';
    }

    protected function definition(): WidgetExtensionDefinitionData
    {
        return new WidgetExtensionDefinitionData(key: 'capell-app.audio-playlist', packageName: self::$packageName, stateVersion: 1, filamentWidget: AudioPlaylistWidget::class, inputData: AudioPlaylistInputData::class, renderData: AudioPlaylistRenderData::class, fallbackView: 'capell-widget-audio-playlist::widget', components: ['blade' => 'capell::widgets.capell-app.audio-playlist'], resourceGroups: [self::resourceGroup()], defaultResourceLoadingStrategy: PresentationLoadingStrategy::Visible, capabilities: new WidgetExtensionCapabilitiesData(requiresInstanceIdentity: true, presentationCapabilities: [WidgetPresentationCapability::Width, WidgetPresentationCapability::Alignment, WidgetPresentationCapability::LoadingStrategy]));
    }
}
