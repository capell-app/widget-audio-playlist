<?php

declare(strict_types=1);
use Capell\WidgetAudioPlaylist\Data\AudioPlaylistInputData;
use Capell\WidgetAudioPlaylist\Health\WidgetAudioPlaylistHealthCheck;
use Capell\WidgetAudioPlaylist\Providers\WidgetAudioPlaylistServiceProvider;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->app->register(WidgetAudioPlaylistServiceProvider::class);
});
it('registers healthy package surfaces', function (): void {
    expect(WidgetAudioPlaylistHealthCheck::passed())->toBeTrue();
});
it('rejects remote audio', function (): void {
    expect(fn () => AudioPlaylistInputData::validateAndCreate(['title' => 'List', 'tracks' => [['title' => 'Track', 'artist' => 'Artist', 'audio' => 'https://remote.test/a.mp3']]]))->toThrow(ValidationException::class);
});
