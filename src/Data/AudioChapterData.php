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
}
