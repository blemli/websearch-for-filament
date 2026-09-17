<?php

namespace Blemli\WebSearch\Engines;

use Blemli\WebSearch\Engines\Concerns\SearchesImages;
use Blemli\WebSearch\Engines\Concerns\SearchesNews;
use Blemli\WebSearch\Engines\Concerns\SearchesVideos;
use Blemli\WebSearch\Engines\Concerns\SearchesWeb;
use Blemli\WebSearch\Enums\SearchType;

class Ecosia extends Engine
{
    use SearchesImages;
    use SearchesNews;
    use SearchesVideos;
    use SearchesWeb;

    public function url(): string
    {
        $path = $this->type === SearchType::Web ? 'search' : $this->type->value;

        return $this->buildUrl("https://www.ecosia.org/{$path}", ['q' => $this->query]);
    }
}
