<?php

namespace Blemli\WebSearch\Engines\Concerns;

use Blemli\WebSearch\Enums\SearchType;

trait SearchesImages
{
    public static function images(string $query): static
    {
        return static::make(SearchType::Images, $query);
    }
}
