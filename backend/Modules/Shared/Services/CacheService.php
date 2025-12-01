<?php

namespace Modules\Shared\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    protected int $ttl = 3600; 

    public function remember(string $key, \Closure $callback, ?int $ttl = null)
    {
        return Cache::remember($key, $ttl ?? $this->ttl, $callback);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function flush(string $pattern = ''): void
    {
        if ($pattern) {
            Cache::tags([$pattern])->flush();
        }
    }
}