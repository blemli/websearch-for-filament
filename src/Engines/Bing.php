<?php

namespace Blemli\WebSearch\Engines;

use Blemli\WebSearch\Engines\Concerns\FiltersByColor;
use Blemli\WebSearch\Engines\Concerns\FiltersByCountry;
use Blemli\WebSearch\Engines\Concerns\FiltersByDuration;
use Blemli\WebSearch\Engines\Concerns\FiltersByLayout;
use Blemli\WebSearch\Engines\Concerns\FiltersByLicense;
use Blemli\WebSearch\Engines\Concerns\FiltersBySize;
use Blemli\WebSearch\Engines\Concerns\FiltersByTimespan;
use Blemli\WebSearch\Engines\Concerns\SearchesImages;
use Blemli\WebSearch\Engines\Concerns\SearchesNews;
use Blemli\WebSearch\Engines\Concerns\SearchesShopping;
use Blemli\WebSearch\Engines\Concerns\SearchesVideos;
use Blemli\WebSearch\Engines\Concerns\SearchesWeb;
use Blemli\WebSearch\Engines\Contracts\HasCountry;
use Blemli\WebSearch\Engines\Contracts\HasImageColor;
use Blemli\WebSearch\Engines\Contracts\HasImageLayout;
use Blemli\WebSearch\Engines\Contracts\HasImageLicense;
use Blemli\WebSearch\Engines\Contracts\HasImageSize;
use Blemli\WebSearch\Engines\Contracts\HasTimespan;
use Blemli\WebSearch\Engines\Contracts\HasVideoDuration;
use Blemli\WebSearch\Enums\Color;
use Blemli\WebSearch\Enums\License;
use Blemli\WebSearch\Enums\SearchType;
use Blemli\WebSearch\Enums\Timespan;
use Carbon\CarbonImmutable;

class Bing extends Engine implements HasCountry, HasImageColor, HasImageLayout, HasImageLicense, HasImageSize, HasTimespan, HasVideoDuration
{
    use FiltersByColor;
    use FiltersByCountry;
    use FiltersByDuration;
    use FiltersByLayout;
    use FiltersByLicense;
    use FiltersBySize;
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
        $base = match ($this->type) {
            SearchType::Web => 'https://www.bing.com/search',
            SearchType::Images => 'https://www.bing.com/images/search',
            SearchType::Videos => 'https://www.bing.com/videos/search',
            SearchType::News => 'https://www.bing.com/news/search',
            SearchType::Shopping => 'https://www.bing.com/shop',
        };

        $filters = $this->type === SearchType::Web ? [] : $this->filterUi();

        return $this->buildUrl($base, [
            'q' => $this->query,
            'qft' => $filters === [] ? null : '+filterui:' . implode('+filterui:', $filters),
            'filters' => $this->type === SearchType::Web ? $this->webDateFilter() : null,
            'cc' => $this->country?->value,
        ]);
    }

    protected function webDateFilter(): ?string
    {
        if ($this->hasDateRange()) {
            $epoch = CarbonImmutable::create(1970, 1, 1);
            $from = (int) $epoch->diffInDays($this->since ?? $epoch);
            $to = (int) $epoch->diffInDays($this->until ?? CarbonImmutable::now());

            return "ex1:\"ez5_{$from}_{$to}\"";
        }

        return match ($this->timespan) {
            Timespan::Day => 'ex1:"ez1"',
            Timespan::Week => 'ex1:"ez2"',
            Timespan::Month => 'ex1:"ez3"',
            default => null,
        };
    }

    /**
     * @return list<string>
     */
    protected function filterUi(): array
    {
        $parts = [];

        if ($this->timespan) {
            $prefix = $this->type === SearchType::Videos ? 'videoage-lt' : 'age-lt';
            $parts[] = $prefix . match ($this->timespan) {
                Timespan::Day => '1440',
                Timespan::Week => '10080',
                Timespan::Month => '43200',
                Timespan::Year => '525600',
            };
        }

        if ($this->type === SearchType::Images) {
            if ($this->license) {
                $parts[] = 'license-' . match ($this->license) {
                    License::PublicDomain => 'L1',
                    License::CreativeCommons => 'L2_L3_L4_L5_L6_L7',
                    License::Commercial => 'L2_L3_L4',
                    License::Modify => 'L2_L3_L5_L6',
                    License::ModifyCommercially => 'L2_L3',
                    License::Any => 'L1',
                };
            }

            if ($this->color) {
                $parts[] = match ($this->color) {
                    Color::Transparent => 'photo-transparent',
                    Color::FullColor => 'color2-color',
                    Color::Monochrome => 'color2-bw',
                    default => 'color2-FGcls_' . strtoupper($this->color->value),
                };
            }

            if ($this->minWidth || $this->minHeight) {
                $parts[] = 'imagesize-custom_' . ($this->minWidth ?? 0) . '_' . ($this->minHeight ?? 0);
            } elseif ($this->size) {
                $parts[] = 'imagesize-' . match ($this->size->value) {
                    'icon', 'small' => 'small',
                    default => $this->size->value,
                };
            }

            if ($layout = $this->firstSupportedLayout(['square' => 'square', 'tall' => 'tall', 'wide' => 'wide', 'panoramic' => 'wide'])) {
                $parts[] = 'aspect-' . $layout;
            }
        }

        if ($this->type === SearchType::Videos && $this->duration) {
            $parts[] = 'videolength-' . $this->duration->value;
        }

        return $parts;
    }
}
