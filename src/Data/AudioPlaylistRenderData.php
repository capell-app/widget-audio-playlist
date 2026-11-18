<?php

declare(strict_types=1);

namespace Capell\WidgetAudioPlaylist\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class AudioPlaylistRenderData extends Data
{
    /** @param DataCollection<int, AudioTrackRenderData> $tracks */
    public function __construct(public string $title, #[DataCollectionOf(AudioTrackRenderData::class)] public DataCollection $tracks) {}
}
