<?php

declare(strict_types=1);

namespace Capell\WidgetAudioPlaylist;

use Capell\Core\Contracts\Extensions\RegistersExtensionContentWidget;

final class WidgetAudioPlaylistContribution implements RegistersExtensionContentWidget
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^1.0';
    }
}
