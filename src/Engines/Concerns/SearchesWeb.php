<?php

namespace Blemli\WebSearch\Engines\Concerns;

use Blemli\WebSearch\Enums\SearchType;

trait SearchesWeb
{
    public static function web(string $query): static
    {
        return static::make(SearchType::Web, $query);
    }
}
