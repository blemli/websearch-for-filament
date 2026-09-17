<?php

namespace Blemli\WebSearch\Engines;

use Blemli\WebSearch\Engines\Concerns\FiltersByCountry;
use Blemli\WebSearch\Engines\Concerns\FiltersByTimespan;
use Blemli\WebSearch\Engines\Concerns\SearchesImages;
use Blemli\WebSearch\Engines\Concerns\SearchesNews;
use Blemli\WebSearch\Engines\Concerns\SearchesShopping;
use Blemli\WebSearch\Engines\Concerns\SearchesVideos;
use Blemli\WebSearch\Engines\Concerns\SearchesWeb;
use Blemli\WebSearch\Engines\Contracts\HasCountry;
use Blemli\WebSearch\Engines\Contracts\HasTimespan;
use Blemli\WebSearch\Enums\SearchType;
use Blemli\WebSearch\Enums\Timespan;

class Swisscows extends Engine implements HasCountry, HasTimespan
{
    use FiltersByCountry;
    use FiltersByTimespan;
    use SearchesImages;
    use SearchesNews;
    use SearchesShopping;
    use SearchesVideos;
    use SearchesWeb;

    public static function canBeEmbedded(): bool
    {
        return true;
    }

    public function url(): string
    {
        $path = match ($this->type) {
            SearchType::Web => 'web',
            SearchType::Images => 'images',
            SearchType::Videos => 'video',
            SearchType::News => 'news',
            SearchType::Shopping => 'shopping',
        };

        return $this->buildUrl("https://swisscows.com/{$this->appLanguage()}/{$path}", [
            'query' => $this->query,
            'region' => $this->country ? $this->appLanguage() . '-' . strtoupper($this->country->value) : null,
            'freshness' => match ($this->timespan) {
                Timespan::Day => 'Day',
                Timespan::Week => 'Week',
                Timespan::Month, Timespan::Year => 'Month',
                null => null,
            },
        ]);
    }
}
