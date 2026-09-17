<?php

namespace Blemli\WebSearch\Engines\Concerns;

use Blemli\WebSearch\Enums\SearchType;

trait SearchesNews
{
    public static function news(string $query): static
    {
        return static::make(SearchType::News, $query);
    }
}
