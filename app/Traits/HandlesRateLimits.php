<?php

namespace App\Traits;

use App\Exceptions\RateLimitException;
use Illuminate\Support\Facades\Cache;

trait HandlesRateLimits
{
    protected function checkRateLimit(string $key, int $maxAttempts = 60, int $decayMinutes = 1): void
    {
        $attempts = Cache::get($key, 0);

        if ($attempts >= $maxAttempts) {
            $retryAfter = Cache::get($key . ':retry_after', 60);
            throw new RateLimitException($retryAfter);
        }

        Cache::put($key, $attempts + 1, $decayMinutes * 60);
    }

    protected function getRateLimitKey(string $operation): string
    {
        return 'rate_limit:' . $operation . ':' . request()->ip();
    }
}