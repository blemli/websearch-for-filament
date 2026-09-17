<?php

namespace Blemli\WebSearch\Stores;

use Blemli\WebSearch\Models\Preference;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * The package's own table — publish and run the migration first.
 */
class DatabaseStore implements PreferenceStore
{
    public function get(?Authenticatable $user): array
    {
        if (! $user instanceof Model) {
            return [];
        }

        $preference = Preference::for($user);

        return $preference ? array_filter($preference->only(['engine', 'open_in', 'country'])) : [];
    }

    public function put(?Authenticatable $user, array $preferences): void
    {
        if (! $user instanceof Model) {
            return;
        }

        Preference::updateFor($user, [
            'engine' => $preferences['engine'] ?? null,
            'open_in' => $preferences['open_in'] ?? null,
            'country' => $preferences['country'] ?? null,
        ]);
    }
}
