<?php

namespace Blemli\WebSearch\Engines;

use Blemli\WebSearch\Engines\Concerns\FiltersByTimespan;
use Blemli\WebSearch\Engines\Concerns\SearchesImages;
use Blemli\WebSearch\Engines\Concerns\SearchesNews;
use Blemli\WebSearch\Engines\Concerns\SearchesVideos;
use Blemli\WebSearch\Engines\Concerns\SearchesWeb;
use Blemli\WebSearch\Engines\Contracts\HasTimespan;
use Blemli\WebSearch\Enums\SearchType;

class Startpage extends Engine implements HasTimespan
{
    use FiltersByTimespan;
    use SearchesImages;
    use SearchesNews;
    use SearchesVideos;
    use SearchesWeb;

    public function url(): string
    {
        return $this->buildUrl('https://www.startpage.com/do/search', [
            'q' => $this->query,
            'cat' => match ($this->type) {
                SearchType::Web => 'web',
                SearchType::Images => 'images',
                SearchType::Videos => 'video',
                SearchType::News => 'news',
                default => 'web',
            },
            'with_date' => $this->timespan?->value[0],
        ]);
    }
}
