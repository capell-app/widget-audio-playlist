<?php

declare(strict_types=1);

namespace Capell\WidgetAudioPlaylist\Actions;

use Capell\Core\Actions\Media\ResolveResponsiveImageAction;
use Capell\LayoutBuilder\Contracts\WidgetExtensions\WidgetExtensionBatchPayloadResolver;
use Capell\LayoutBuilder\Data\WidgetExtensions\WidgetExtensionPayloadBatchData;
use Capell\WidgetAudioPlaylist\Data\AudioPlaylistInputData;
use Capell\WidgetAudioPlaylist\Data\AudioPlaylistRenderData;
use Capell\WidgetAudioPlaylist\Data\AudioTrackRenderData;
use Override;

final class ResolveAudioPlaylistPayloadsAction implements WidgetExtensionBatchPayloadResolver
{
    /** @return array<string, AudioPlaylistRenderData> */
    #[Override]
    public function resolve(WidgetExtensionPayloadBatchData $batch): array
    {
        $payloads = [];

        foreach ($batch->items as $item) {
            if (! $item->input instanceof AudioPlaylistInputData) {
                continue;
            }

            $tracks = [];
            foreach ($item->input->tracks as $track) {
                $size = $track->artwork === null ? null : ResolveResponsiveImageAction::run($track->artwork);
                $tracks[] = new AudioTrackRenderData(
                    title: $track->title,
                    artist: $track->artist,
                    audio: $track->audio,
                    artwork: $track->artwork,
                    transcript: $track->transcript,
                    chapters: $track->chapters,
                    artworkWidth: $size?->width,
                    artworkHeight: $size?->height,
                    artworkSrcset: $size?->srcset,
                );
            }

            $payloads[$item->instanceId] = AudioPlaylistRenderData::from(['title' => $item->input->title, 'tracks' => $tracks]);
        }

        return $payloads;
    }
}
