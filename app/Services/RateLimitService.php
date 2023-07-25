<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class RateLimitService
{
    const CACHE_KEY = 'recovery:services:rate-limit:';

    public static function check($ip)
    {
        $key = self::CACHE_KEY . 'byip:' . $ip;
        $val = Cache::get($key);
        if(!$val) {
            return 0;
        }
        return (int) $val;
    }

    public static function increment($ip)
    {
        $key = self::CACHE_KEY . 'byip:' . $ip;
        $val = Cache::get($key);
        $ttl = config('config.rate_limit.ttl', 14400);
        if(!$val) {
            Cache::put($key, 1, $ttl);
            return 1;
        }
        Cache::put($key, $val + 1, $ttl);
        return (int) $val + 1;
    }
}
