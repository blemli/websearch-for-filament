<?php

namespace Blemli\WebSearch\Engines;

use Blemli\WebSearch\Engines\Concerns\FiltersByCountry;
use Blemli\WebSearch\Engines\Concerns\FiltersByTimespan;
use Blemli\WebSearch\Engines\Concerns\SearchesImages;
use Blemli\WebSearch\Engines\Concerns\SearchesNews;
use Blemli\WebSearch\Engines\Concerns\SearchesVideos;
use Blemli\WebSearch\Engines\Concerns\SearchesWeb;
use Blemli\WebSearch\Engines\Contracts\HasCountry;
use Blemli\WebSearch\Engines\Contracts\HasTimespan;
use Blemli\WebSearch\Enums\SearchType;

class Brave extends Engine implements HasCountry, HasTimespan
{
    use FiltersByCountry;
    use FiltersByTimespan;
    use SearchesImages;
    use SearchesNews;
    use SearchesVideos;
    use SearchesWeb;

    public function url(): string
    {
        $path = match ($this->type) {
            SearchType::Web => 'search',
            default => $this->type->value,
        };

        return $this->buildUrl("https://search.brave.com/{$path}", [
            'q' => $this->query,
            'tf' => $this->tf(),
            'country' => $this->country?->value,
        ]);
    }

    protected function tf(): ?string
    {
        if ($this->hasDateRange()) {
            return ($this->since?->format('Y-m-d') ?? '') . 'to' . ($this->until?->format('Y-m-d') ?? '');
        }

        return $this->timespan ? 'p' . $this->timespan->value[0] : null;
    }
}
