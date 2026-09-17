<?php

namespace Blemli\WebSearch\Listeners;

use Blemli\WebSearch\Events\SearchOpened;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes a "websearch" activity when spatie/laravel-activitylog is
 * installed; a no-op otherwise.
 */
class LogSearchToActivitylog
{
    public function handle(SearchOpened $event): void
    {
        if (! function_exists('activity')) {
            return;
        }

        $activity = activity('websearch')
            ->withProperties([
                'engine' => $event->engine::key(),
                'type' => $event->type->value,
                'query' => $event->query,
                'url' => $event->url,
            ]);

        if ($event->user instanceof Model) {
            $activity->causedBy($event->user);
        }

        $activity->log('search');
    }
}
