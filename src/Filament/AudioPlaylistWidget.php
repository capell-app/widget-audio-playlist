<?php

declare(strict_types=1);

namespace Capell\WidgetAudioPlaylist\Filament;

use Capell\Admin\Contracts\Widgets\FilamentWidget;
use Capell\LayoutBuilder\Filament\Components\Forms\WidgetFileUpload;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

final class AudioPlaylistWidget implements FilamentWidget
{
    public static function getWidgetName(): string
    {
        return 'capell-app.audio-playlist';
    }

    public static function make(): Block
    {
        return Block::make(self::getWidgetName())->label(__('capell-widget-audio-playlist::widget.label'))->schema([TextInput::make('title')->required()->maxLength(120), Repeater::make('tracks')->minItems(1)->maxItems(20)->reorderable()->schema([TextInput::make('title')->required()->maxLength(120), TextInput::make('artist')->required()->maxLength(120), WidgetFileUpload::make('audio')->acceptedFileTypes(['audio/mpeg', 'audio/mp4', 'audio/ogg', 'audio/wav'])->required(), WidgetFileUpload::make('artwork')->image(), Textarea::make('transcript')->maxLength(20000), Repeater::make('chapters')->maxItems(50)->schema([TextInput::make('label')->required()->maxLength(100), TextInput::make('startSeconds')->integer()->minValue(0)->maxValue(86400)->required()])])]);
    }
}
