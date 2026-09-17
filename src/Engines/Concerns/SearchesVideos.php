<?php

namespace Blemli\WebSearch\Engines\Concerns;

use Blemli\WebSearch\Enums\SearchType;

trait SearchesVideos
{
    public static function videos(string $query): static
    {
        return static::make(SearchType::Videos, $query);
    }
}
