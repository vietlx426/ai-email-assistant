<?php

namespace App\Services;

use App\Contracts\CacheServiceInterface;
use Illuminate\Support\Facades\Cache;

class CacheService implements CacheServiceInterface
{
    protected string $prefix = 'email_assistant';

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::get($this->prefixKey($key), $default);
    }

    public function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        return Cache::put($this->prefixKey($key), $value, $ttl);
    }

    public function forget(string $key): bool
    {
        return Cache::forget($this->prefixKey($key));
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        return Cache::remember($this->prefixKey($key), $ttl, $callback);
    }

    public function flush(): bool
    {
        return Cache::flush();
    }

    protected function prefixKey(string $key): string
    {
        return "{$this->prefix}:{$key}";
    }
}
