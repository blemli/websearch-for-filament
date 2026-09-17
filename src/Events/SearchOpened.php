<?php

namespace Blemli\WebSearch\Events;

use Blemli\WebSearch\Engines\Engine;
use Blemli\WebSearch\Enums\SearchType;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;

class SearchOpened
{
    use Dispatchable;

    /**
     * @param  class-string<Engine>  $engine
     */
    public function __construct(
        public readonly ?Authenticatable $user,
        public readonly string $engine,
        public readonly SearchType $type,
        public readonly string $query,
        public readonly string $url,
    ) {}
}
