<?php

declare(strict_types=1);

namespace Capell\WidgetAudioPlaylist\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class AudioTrackRenderData extends Data
{
    /** @param DataCollection<int, AudioChapterData> $chapters */
    public function __construct(
        public string $title,
        public string $artist,
        public string $audio,
        public ?string $artwork = null,
        public ?string $transcript = null,
        #[DataCollectionOf(AudioChapterData::class)]
        public DataCollection $chapters = new DataCollection(AudioChapterData::class, []),
        public ?int $artworkWidth = null,
        public ?int $artworkHeight = null,
        public ?string $artworkSrcset = null,
    ) {}

    /** @return list<string> */
    public function transcriptParagraphs(): array
    {
        return array_values(array_filter(
            array_map(trim(...), preg_split('/\R{2,}/', (string) $this->transcript) ?: []),
            static fn (string $paragraph): bool => $paragraph !== '',
        ));
    }
}
