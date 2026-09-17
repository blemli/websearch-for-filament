<?php

namespace Blemli\WebSearch\Stores;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * A JSON column on the user model, cast to array by the host.
 */
class AttributeStore implements PreferenceStore
{
    public function __construct(
        protected string $attribute = 'websearch_preferences',
    ) {}

    public function get(?Authenticatable $user): array
    {
        if (! $user instanceof Model) {
            return [];
        }

        $value = $user->getAttribute($this->attribute);

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        return is_array($value) ? $value : [];
    }

    public function put(?Authenticatable $user, array $preferences): void
    {
        if (! $user instanceof Model) {
            return;
        }

        $user->forceFill([$this->attribute => $preferences])->save();
    }
}
