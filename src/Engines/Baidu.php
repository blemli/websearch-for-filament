<?php

namespace Blemli\WebSearch\Engines;

use Blemli\WebSearch\Engines\Concerns\SearchesImages;
use Blemli\WebSearch\Engines\Concerns\SearchesNews;
use Blemli\WebSearch\Engines\Concerns\SearchesVideos;
use Blemli\WebSearch\Engines\Concerns\SearchesWeb;
use Blemli\WebSearch\Enums\SearchType;

class Baidu extends Engine
{
    use SearchesImages;
    use SearchesNews;
    use SearchesVideos;
    use SearchesWeb;

    public static function canBeEmbedded(): bool
    {
        return true;
    }

    public function url(): string
    {
        return match ($this->type) {
            SearchType::Images => $this->buildUrl('https://image.baidu.com/search/index', ['tn' => 'baiduimage', 'word' => $this->query]),
            SearchType::Videos => $this->buildUrl('https://www.baidu.com/sf/vsearch', ['tn' => 'vsearch', 'wd' => $this->query]),
            SearchType::News => $this->buildUrl('https://www.baidu.com/s', ['tn' => 'news', 'rtt' => 1, 'bsst' => 1, 'cl' => 2, 'word' => $this->query]),
            default => $this->buildUrl('https://www.baidu.com/s', ['wd' => $this->query]),
        };
    }
}
