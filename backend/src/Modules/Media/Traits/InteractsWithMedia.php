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
        $media = $this->media()->where('collection_name', $collection)->first();
        return $media ? $media->url : null;
    }
}