<?php

namespace Blemli\WebSearch\Engines\Concerns;

use Blemli\WebSearch\Enums\SearchType;

trait SearchesShopping
{
    public static function shopping(string $query): static
    {
        return static::make(SearchType::Shopping, $query);
    }
}
