<?php

declare(strict_types=1);

namespace Modules\Shared\Services;

use Illuminate\Support\Facades\Cache;
use Closure;

class CacheService
{
    protected int $defaultTtl = 3600;

    public function remember(string $key, Closure $callback, ?int $ttl = null): mixed
    {
        return Cache::remember($key, $ttl ?? $this->defaultTtl, $callback);
    }

    public function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    public function flush(?string $tag = null): void
    {
        if ($tag) {
            Cache::tags([$tag])->flush();
        } else {
            Cache::flush();
        }
    }
}