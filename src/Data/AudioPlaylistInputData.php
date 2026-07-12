<?php

declare(strict_types=1);

namespace Capell\WidgetAudioPlaylist\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class AudioPlaylistInputData extends Data
{
    /** @param DataCollection<int, AudioTrackData> $tracks */
    public function __construct(public string $title, #[DataCollectionOf(AudioTrackData::class)] public DataCollection $tracks) {}

    /** @return array<string, list<string>> */
    public static function rules(): array
    {
        return ['title' => ['required', 'string', 'max:120'], 'tracks' => ['required', 'array', 'min:1', 'max:20']];
    }
}
