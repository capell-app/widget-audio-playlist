<?php

declare(strict_types=1);

use Capell\WidgetAudioPlaylist\Data\AudioChapterData;
use Capell\WidgetAudioPlaylist\Data\AudioPlaylistRenderData;
use Capell\WidgetAudioPlaylist\Data\AudioTrackData;
use Capell\WidgetCountdown\Data\CountdownRenderData;
use Capell\WidgetLocationMap\Data\LocationMapRenderData;
use Capell\WidgetLocationMap\Data\LocationMarkerData;
use Capell\WidgetSlideshow\Data\SlideshowRenderData;
use Capell\WidgetSlideshow\Data\SlideshowSlideData;
use Dom\Element;
use Dom\HTMLDocument;
use Dom\Node;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Spatie\LaravelData\DataCollection;

require dirname(__DIR__, 4) . '/vendor/autoload.php';

// Render the shipped Blade without a database or a shared Testbench cache.
$slug = $argv[1];
$root = dirname(__DIR__, 4) . '/packages/' . $slug . '/resources';
$cache = sys_get_temp_dir() . '/widget-blade-' . bin2hex(random_bytes(8));
mkdir($cache);
$files = new Filesystem;
// The widgets read the locale through app() and the Lang facade, so use a real application.
$container = new Application(dirname(__DIR__, 4));
Container::setInstance($container);
$container->instance('config', new Repository(['app' => ['locale' => 'en', 'fallback_locale' => 'en']]));
$container->instance('url', new UrlGenerator(new RouteCollection, Request::create('http://localhost')));
$loader = new FileLoader($files, $root . '/lang');
$loader->addNamespace('capell-' . $slug, $root . '/lang');
$translator = new Translator($loader, 'en');
$translator->setFallback('en');
$container->instance('translator', $translator);
Facade::setFacadeApplication($container);
$engines = new EngineResolver;
$engines->register('php', static fn (): PhpEngine => new PhpEngine($files));
$engines->register('blade', static fn (): CompilerEngine => new CompilerEngine(new BladeCompiler($files, $cache)));
$finder = new FileViewFinder($files, [$root . '/views']);
$finder->addNamespace('capell-' . $slug, $root . '/views');
$views = new Factory($engines, $finder, new Dispatcher($container));
$views->setContainer($container);
$views->share('__env', $views);
$container->instance('view', $views);

try {
    $options = json_decode((string) stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
    if (! is_array($options)) {
        throw new InvalidArgumentException('Fixture options must be an object.');
    }

    $controls = $options['controls'] ?? true;
    $auto = $options['auto'] ?? false;
    if (! is_bool($controls) || ! is_bool($auto)) {
        throw new InvalidArgumentException('Playback options must be boolean.');
    }

    $view = match ($slug) {
        'widget-audio-playlist' => 'capell-widget-audio-playlist::widget',
        'widget-slideshow' => 'capell-widget-slideshow::widget',
        'widget-countdown' => 'capell-widget-countdown::widget',
        'widget-location-map' => 'capell-widget-location-map::widget',
        default => throw new InvalidArgumentException('Unknown widget fixture.'),
    };
    $data = match ($slug) {
        'widget-audio-playlist' => new AudioPlaylistRenderData('Listen', new DataCollection(AudioTrackData::class, array_map(static fn (int $number): AudioTrackData => new AudioTrackData('Track ' . $number, 'Artist', 'audio/local.mp3', chapters: new DataCollection(AudioChapterData::class, [new AudioChapterData('Opening', 12), new AudioChapterData('Next', 35)])), [1, 2]))),
        'widget-slideshow' => new SlideshowRenderData('Slides', new DataCollection(SlideshowSlideData::class, [new SlideshowSlideData('one.jpg', 'One'), new SlideshowSlideData('two.jpg', 'Two', ctaLabel: 'Visit', ctaUrl: '/visit')]), $controls, $auto),
        'widget-countdown' => new CountdownRenderData('Countdown', 'UTC', '2030-01-01T00:01:00+00:00', 'Finished'),
        'widget-location-map' => new LocationMapRenderData('Map', new DataCollection(LocationMarkerData::class, [new LocationMarkerData('Studio', 51.5, -0.1)])),
    };
    $html = $views->make($view, ['renderData' => $data])->render();
    if (($argv[2] ?? '') === 'tree') {
        $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
        $tree = static function (Element $element) use (&$tree): array {
            $attributes = [];
            foreach ($element->attributes as $attribute) {
                $attributes[$attribute->name] = $attribute->value;
            }

            return ['tag' => $element->localName, 'attributes' => $attributes, 'text' => $element->textContent, 'children' => array_map($tree, array_values(array_filter(iterator_to_array($element->childNodes), static fn (Node $node): bool => $node instanceof Element)))];
        };
        $rootElement = $document->documentElement;
        if (! $rootElement instanceof Element) {
            throw new RuntimeException('Widget fixture has no root.');
        }

        echo json_encode($tree($rootElement), JSON_THROW_ON_ERROR);
    } else {
        echo $html;
    }
} finally {
    $files->deleteDirectory($cache);
}
