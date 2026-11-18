@php /** @var \Capell\WidgetAudioPlaylist\Data\AudioPlaylistRenderData $renderData */ @endphp
<section
    class="capell-audio-playlist"
    data-capell-widget-audio-playlist
    data-playback-error="{{ __('capell-widget-audio-playlist::widget.playback_error') }}"
>
    <h2>{{ $renderData->title }}</h2>
    <ol>
        @foreach ($renderData->tracks as $track)
            <li data-audio-track>
                @if ($track->artwork)
                    <img
                        src="{{ asset('storage/'.ltrim($track->artwork,'/')) }}"
                        alt=""
                        @if ($track->artworkSrcset) srcset="{{ $track->artworkSrcset }}" sizes="8rem" @endif
                        @if ($track->artworkWidth && $track->artworkHeight) width="{{ $track->artworkWidth }}" height="{{ $track->artworkHeight }}" @endif
                        decoding="async"
                        loading="lazy"
                    />
                @endif
                <div>
                    <h3>{{ $track->title }}</h3>
                    <p>{{ $track->artist }}</p>
                    <audio
                        controls
                        preload="none"
                        src="{{ asset('storage/'.ltrim($track->audio,'/')) }}"
                    ></audio>
                    <p role="status" aria-live="polite" data-audio-status></p>
                    @if ($track->transcript)
                        <details>
                            <summary>
                                {{ __('capell-widget-audio-playlist::widget.transcript') }}
                            </summary>
                            @foreach ($track->transcriptParagraphs() as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </details>
                    @endif
                    @if ($track->chapters->count())
                        <details>
                            <summary>
                                {{ __('capell-widget-audio-playlist::widget.chapters') }}
                            </summary>
                            <ol>
                                @foreach ($track->chapters as $chapter)
                                    <li>
                                        <button
                                            type="button"
                                            data-audio-seek="{{ $chapter->startSeconds }}"
                                        >
                                            {{ $chapter->label }} ({{ $chapter->timestamp() }})
                                        </button>
                                    </li>
                                @endforeach
                            </ol>
                        </details>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</section>
