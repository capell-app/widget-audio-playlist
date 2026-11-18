<?php

declare(strict_types=1);

use Capell\Core\Enums\ContentStructure;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\Frontend\Actions\AssertPublicHtmlContainsNoAuthoringSurfaceAction;
use Capell\Frontend\Support\Security\PublicHtmlSafetyInspector;
use Capell\LayoutBuilder\Data\WidgetExtensions\WidgetExtensionPayloadBatchData;
use Capell\LayoutBuilder\Data\WidgetExtensions\WidgetExtensionPayloadInputData;
use Capell\LayoutBuilder\Data\WidgetExtensions\WidgetExtensionRenderContextData;
use Capell\WidgetAudioPlaylist\Actions\ResolveAudioPlaylistPayloadsAction;
use Capell\WidgetAudioPlaylist\Data\AudioPlaylistInputData;
use Capell\WidgetAudioPlaylist\Data\AudioPlaylistRenderData;
use Capell\WidgetAudioPlaylist\Health\WidgetAudioPlaylistHealthCheck;
use Capell\WidgetAudioPlaylist\Providers\WidgetAudioPlaylistServiceProvider;
use Dom\HTMLDocument;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

it('shows long chapter times with hours, splits the transcript into paragraphs and sizes artwork', function (): void {
    $renderData = AudioPlaylistRenderData::from(['title' => 'Listen', 'tracks' => [[
        'title' => 'Long', 'artist' => 'Artist', 'audio' => 'audio/long.mp3', 'artwork' => 'art/cover.png',
        'artworkWidth' => 600, 'artworkHeight' => 600,
        'transcript' => "Welcome <b>everyone</b>\n\nSecond paragraph",
        'chapters' => [['label' => 'Intro', 'startSeconds' => 65], ['label' => 'Finale', 'startSeconds' => 3725]],
    ]]]);
    $html = view('capell-widget-audio-playlist::widget', ['renderData' => $renderData])->render();

    expect($html)->toContain('Intro (01:05)')
        ->toContain('Finale (1:02:05)')
        ->toContain('<p>Welcome &lt;b&gt;everyone&lt;/b&gt;</p>')
        ->toContain('<p>Second paragraph</p>')
        ->toContain('width="600"')
        ->toContain('height="600"');
});

it('resolves the artwork size into render data and ignores tracks without artwork or with traversing paths', function (): void {
    Storage::fake('public');
    ob_start();
    imagepng(imagecreatetruecolor(300, 200));
    Storage::disk('public')->put('art/cover.png', (string) ob_get_clean());
    $input = AudioPlaylistInputData::validateAndCreate(['title' => 'Listen', 'tracks' => [
        ['title' => 'A', 'artist' => 'X', 'audio' => 'audio/a.mp3', 'artwork' => 'art/cover.png'],
        ['title' => 'B', 'artist' => 'X', 'audio' => 'audio/b.mp3'],
        ['title' => 'C', 'artist' => 'X', 'audio' => 'audio/c.mp3', 'artwork' => '../cover.png'],
    ]]);
    $payload = resolve(ResolveAudioPlaylistPayloadsAction::class)->resolve(new WidgetExtensionPayloadBatchData([new WidgetExtensionPayloadInputData('instance', $input)], new WidgetExtensionRenderContextData(null)))['instance'];

    expect($payload->tracks[0]->artworkWidth)->toBe(300)
        ->and($payload->tracks[0]->artworkHeight)->toBe(200)
        ->and($payload->tracks[1]->artworkWidth)->toBeNull()
        ->and($payload->tracks[2]->artworkWidth)->toBeNull();
});

it('serves a srcset for wide artwork and keeps missing artwork plain, with no authoring state', function (): void {
    Storage::fake('public');
    ob_start();
    imagejpeg(imagecreatetruecolor(1600, 1600));
    Storage::disk('public')->put('art/wide.jpg', (string) ob_get_clean());
    $input = AudioPlaylistInputData::validateAndCreate(['title' => 'Listen', 'tracks' => [
        ['title' => 'A', 'artist' => 'X', 'audio' => 'audio/a.mp3', 'artwork' => 'art/wide.jpg'],
        ['title' => 'B', 'artist' => 'X', 'audio' => 'audio/b.mp3', 'artwork' => 'art/missing.jpg'],
    ]]);
    $payload = resolve(ResolveAudioPlaylistPayloadsAction::class)->resolve(new WidgetExtensionPayloadBatchData([new WidgetExtensionPayloadInputData('instance', $input)], new WidgetExtensionRenderContextData(null)))['instance'];
    $html = view('capell-widget-audio-playlist::widget', ['renderData' => $payload])->render();
    $images = HTMLDocument::createFromString($html, LIBXML_NOERROR)->querySelectorAll('img');

    expect($images[0]->getAttribute('srcset'))->toContain(' 480w')->toContain('art/wide.jpg 1600w')
        ->and($images[0]->getAttribute('sizes'))->toBe('8rem')
        ->and($images[1]->hasAttribute('srcset'))->toBeFalse()
        ->and($html)->not->toContain('instance')->not->toContain(Storage::disk('public')->path(''));
});
