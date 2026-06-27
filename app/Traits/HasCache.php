<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasCache
{
    protected static function bootHasCache()
    {
        static::saved(function ($model) {
            $model->clearModelCache();
        });

        static::deleted(function ($model) {
            $model->clearModelCache();
        });
    }

    public function getCacheKey(string $key = null): string
    {
        $base = $this->getTable() . '_' . $this->getKey();
        return $key ? $base . '_' . $key : $base;
    }

    public function cacheFor(string $key, $value, int $ttl = 3600)
    {
        return Cache::remember($this->getCacheKey($key), $ttl, $value);
    }

    public function getCached(string $key, $default = null)
    {
        return Cache::get($this->getCacheKey($key), $default);
    }

    public function putCache(string $key, $value, int $ttl = 3600): void
    {
        Cache::put($this->getCacheKey($key), $value, $ttl);
    }

    public function forgetCache(string $key = null): void
    {
        if ($key) {
            Cache::forget($this->getCacheKey($key));
        } else {
            // Clear all cache for this model
            $keys = Cache::get($this->getCacheKey('keys'), []);
            foreach ($keys as $key) {
                Cache::forget($this->getCacheKey($key));
            }
            Cache::forget($this->getCacheKey('keys'));
        }
    }

    protected function clearModelCache(): void
    {
        // Override in child classes to clear specific cache keys
    }
}