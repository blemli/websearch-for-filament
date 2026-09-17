<?php

namespace Blemli\WebSearch\Stores;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cookie;

/**
 * Migration-less default: an encrypted cookie, per browser, kept a year.
 */
class CookieStore implements PreferenceStore
{
    public const NAME = 'websearch_preferences';

    public function get(?Authenticatable $user): array
    {
        $raw = request()->cookie(self::NAME);
        $decoded = is_string($raw) ? json_decode($raw, true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    public function put(?Authenticatable $user, array $preferences): void
    {
        Cookie::queue(Cookie::make(self::NAME, (string) json_encode($preferences), 60 * 24 * 365));
    }
}
