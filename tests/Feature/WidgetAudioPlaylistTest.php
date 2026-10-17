<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Frontend\Actions\AssertPublicHtmlContainsNoAuthoringSurfaceAction;
use Capell\Frontend\Support\Security\PublicHtmlSafetyInspector;
use Capell\WidgetAudioPlaylist\Data\AudioPlaylistInputData;
use Capell\WidgetAudioPlaylist\Data\AudioPlaylistRenderData;
use Capell\WidgetAudioPlaylist\Health\WidgetAudioPlaylistHealthCheck;
use Capell\WidgetAudioPlaylist\Providers\WidgetAudioPlaylistServiceProvider;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    CapellCore::forcePackageInstalled('capell-app/widget-audio-playlist');
    $this->app->register(WidgetAudioPlaylistServiceProvider::class);
});
it('registers healthy package surfaces', function (): void {
    expect(WidgetAudioPlaylistHealthCheck::passed())->toBeTrue();
});
it('rejects remote audio', function (): void {
    expect(fn (): AudioPlaylistInputData => AudioPlaylistInputData::validateAndCreate(['title' => 'List', 'tracks' => [['title' => 'Track', 'artist' => 'Artist', 'audio' => 'https://remote.test/a.mp3']]]))->toThrow(ValidationException::class);
});

it('renders public audio-playlist markup accepted by the public HTML safety action', function (): void {
    $renderData = AudioPlaylistRenderData::from(['title' => 'Listen', 'tracks' => [['title' => 'First track', 'artist' => 'Artist', 'audio' => 'audio/first.mp3']]]);
    $html = view('capell-widget-audio-playlist::widget', ['renderData' => $renderData])->render();

    expect($html)->toContain('data-capell-widget-audio-playlist')
        ->not->toContain('data-capell-audio-playlist');

    AssertPublicHtmlContainsNoAuthoringSurfaceAction::run(new Response(
        '<!doctype html><html><body>' . $html . '</body></html>',
        Response::HTTP_OK,
        ['Content-Type' => 'text/html'],
    ));

    expect(resolve(PublicHtmlSafetyInspector::class)->containsAuthoringSurface(
        '<section data-capell-audio-playlist>Widget</section>',
    ))->toBeTrue();
});
