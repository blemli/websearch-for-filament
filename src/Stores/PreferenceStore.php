<?php

namespace Blemli\WebSearch\Stores;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Where a user's engine choice lives. The cookie store needs nothing, the
 * attribute store a JSON column on the user, the database store the
 * package migration.
 */
interface PreferenceStore
{
    /**
     * @return array{engine?: string | null, open_in?: string | null, country?: string | null}
     */
    public function get(?Authenticatable $user): array;

    /**
     * @param  array{engine?: string | null, open_in?: string | null, country?: string | null}  $preferences
     */
    public function put(?Authenticatable $user, array $preferences): void;
}
