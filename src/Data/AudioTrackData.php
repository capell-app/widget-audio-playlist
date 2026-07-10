<?php

declare(strict_types=1);

namespace Capell\WidgetAudioPlaylist\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class AudioTrackData extends Data
{
    /** @param DataCollection<int, AudioChapterData> $chapters */
    public function __construct(public string $title, public string $artist, public string $audio, public ?string $artwork = null, public ?string $transcript = null, #[DataCollectionOf(AudioChapterData::class)] public DataCollection $chapters = new DataCollection(AudioChapterData::class, [])) {}

    /** @return array<string, list<string>> */
    public static function rules(): array
    {
        $local = ['required', 'string', 'max:2048', 'regex:/^(?![a-z][a-z0-9+.-]*:)(?!\/\/)[A-Za-z0-9_\/.-]+$/i'];

        return ['title' => ['required', 'string', 'max:120'], 'artist' => ['required', 'string', 'max:120'], 'audio' => $local, 'artwork' => ['nullable', 'string', 'max:2048', 'regex:/^(?![a-z][a-z0-9+.-]*:)(?!\/\/)[A-Za-z0-9_\/.-]+$/i'], 'transcript' => ['nullable', 'string', 'max:20000'], 'chapters' => ['array', 'max:50']];
    }
}
