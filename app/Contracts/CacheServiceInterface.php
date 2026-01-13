<?php

namespace App\Contracts;

interface CacheServiceInterface
{
    public function get(string $key, mixed $default = null): mixed;
    public function put(string $key, mixed $value, int $ttl = 3600): bool;
    public function forget(string $key): bool;
    public function remember(string $key, int $ttl, callable $callback): mixed;
    public function flush(): bool;
}