<?php

declare(strict_types=1);

namespace Capell\WidgetAudioPlaylist\Data;

use Spatie\LaravelData\Data;

final class AudioChapterData extends Data
{
    public function __construct(public string $label, public int $startSeconds) {}

    /** @return array<string, list<string>> */
    public static function rules(): array
    {
        return ['label' => ['required', 'string', 'max:100'], 'startSeconds' => ['required', 'integer', 'min:0', 'max:86400']];
    }

    /** The start time as m:ss, or h:mm:ss from the first hour, so long recordings do not wrap back to zero. */
    public function timestamp(): string
    {
        return $this->startSeconds >= 3600
            ? sprintf('%d:%02d:%02d', intdiv($this->startSeconds, 3600), intdiv($this->startSeconds % 3600, 60), $this->startSeconds % 60)
            : sprintf('%02d:%02d', intdiv($this->startSeconds, 60), $this->startSeconds % 60);
    }
}
