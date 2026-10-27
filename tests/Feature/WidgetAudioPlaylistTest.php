<?php

declare(strict_types=1);

use Capell\Core\Enums\ContentStructure;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\Frontend\Actions\AssertPublicHtmlContainsNoAuthoringSurfaceAction;
use Capell\Frontend\Support\Security\PublicHtmlSafetyInspector;
use Capell\WidgetAudioPlaylist\Data\AudioPlaylistInputData;
use Capell\WidgetAudioPlaylist\Data\AudioPlaylistRenderData;
use Capell\WidgetAudioPlaylist\Health\WidgetAudioPlaylistHealthCheck;
use Capell\WidgetAudioPlaylist\Providers\WidgetAudioPlaylistServiceProvider;
use Dom\HTMLDocument;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
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

it('serves the complete public widget through a page without authoring data', function (): void {
    $site = Site::factory()->createOne();
    SiteDomain::query()->updateOrCreate(
        ['site_id' => $site->id, 'domain' => 'localhost', 'path' => null],
        ['scheme' => 'http', 'language_id' => $site->language_id, 'status' => true, 'default' => true],
    );
    $page = Page::factory()->site($site)
        ->state(['content_structure_override' => ContentStructure::Blocks->value])
        ->withTranslations($site->language, ['title' => 'Public widget', 'content' => [['type' => 'capell-app.audio-playlist', 'data' => ['__capell' => ['instance_id' => '4924158d-b2cf-4345-b410-130b301c9992', 'state_version' => 1], 'title' => 'Listen', 'tracks' => [['title' => 'First', 'artist' => 'Artist', 'audio' => 'audio/first.mp3', 'chapters' => [['label' => 'Next', 'startSeconds' => 35]]], ['title' => 'Second', 'artist' => 'Artist', 'audio' => 'audio/second.mp3', 'chapters' => [['label' => 'Opening', 'startSeconds' => 12]]]]]]]], slug: '/public-widget', contentStructure: ContentStructure::Blocks)
        ->createOne();
    $blueprint = $page->blueprint;
    $blueprint->forceFill(['meta' => [...($blueprint->meta ?? []), 'content_structure' => ContentStructure::Blocks->value]])->save();
    $response = resolve(Kernel::class)->handle(Request::create('/public-widget'));
    expect($response->getStatusCode())->toBe(200);
    $html = $response->getContent();
    $this->assertIsString($html);
    $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
    expect($document->querySelectorAll('[data-audio-track] audio'))->toHaveCount(2)
        ->and($document->querySelectorAll('[data-audio-track] li button[data-audio-seek]'))->toHaveCount(2);
    AssertPublicHtmlContainsNoAuthoringSurfaceAction::run($response);
});
