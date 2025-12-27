<?php

declare(strict_types=1);

namespace Modules\Media\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Media\Domain\Models\Media;

trait InteractsWithMedia
{
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }

    public function getFirstMediaUrl(string $collection = 'default'): ?string
    {
        $media = $this->media->first(function ($item) use ($collection) {
            return $item->collection_name === $collection;
        });

        if ($media) {
            return $media->url;
        }

        $media = $this->media()->where('collection_name', $collection)->first();

        return $media?->url;
    }

    public function getMediaUrls(string $collection = 'default'): array
    {
        return $this->media
            ->where('collection_name', $collection)
            ->pluck('url')
            ->toArray();
    }
}