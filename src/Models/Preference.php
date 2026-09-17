<?php

namespace Blemli\WebSearch\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property string $user_type
 * @property int | string $user_id
 * @property string | null $engine
 * @property string | null $open_in
 * @property string | null $country
 */
class Preference extends Model
{
    protected $table = 'websearch_preferences';

    protected $guarded = [];

    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    public static function for(Model $user): ?self
    {
        return static::query()
            ->where('user_type', $user->getMorphClass())
            ->where('user_id', $user->getKey())
            ->first();
    }

    /**
     * @param  array<string, string | null>  $attributes
     */
    public static function updateFor(Model $user, array $attributes): self
    {
        return static::query()->updateOrCreate(
            ['user_type' => $user->getMorphClass(), 'user_id' => $user->getKey()],
            $attributes,
        );
    }
}
