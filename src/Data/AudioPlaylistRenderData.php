<?php

declare(strict_types=1);

namespace Capell\WidgetAudioPlaylist\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class AudioPlaylistRenderData extends Data
{
    /** @param DataCollection<int, AudioTrackData> $tracks */
    public function __construct(public string $title, #[DataCollectionOf(AudioTrackData::class)] public DataCollection $tracks) {}
}
