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
use Blemli\WebSearch\Enums\ImageSize;
use Blemli\WebSearch\Enums\SearchType;
use Blemli\WebSearch\Enums\Timespan;

class Google extends Engine implements HasCountry, HasImageColor, HasImageLayout, HasImageLicense, HasImageSize, HasTimespan, HasVideoDuration
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

    public function url(): string
    {
        return $this->buildUrl('https://www.google.com/search', [
            'q' => $this->query,
            'tbm' => match ($this->type) {
                SearchType::Web => null,
                SearchType::Images => 'isch',
                SearchType::Videos => 'vid',
                SearchType::News => 'nws',
                SearchType::Shopping => 'shop',
            },
            'tbs' => implode(',', $this->tbs()) ?: null,
            'cr' => $this->country ? 'country' . strtoupper($this->country->value) : null,
            'gl' => $this->country?->value,
        ]);
    }

    /**
     * @return list<string>
     */
    protected function tbs(): array
    {
        $parts = [];

        if ($this->hasDateRange()) {
            $parts[] = 'cdr:1';
            $parts[] = 'cd_min:' . ($this->since?->format('n/j/Y') ?? '');
            $parts[] = 'cd_max:' . ($this->until?->format('n/j/Y') ?? '');
        } elseif ($this->timespan) {
            $parts[] = 'qdr:' . match ($this->timespan) {
                Timespan::Day => 'd',
                Timespan::Week => 'w',
                Timespan::Month => 'm',
                Timespan::Year => 'y',
            };
        }

        if ($this->type === SearchType::Images) {
            if ($this->license) {
                // Google only distinguishes Creative Commons from "commercial & other".
                $parts[] = 'il:cl';
            }

            if ($this->color) {
                $parts[] = match ($this->color) {
                    Color::Transparent => 'ic:trans',
                    Color::FullColor => 'ic:color',
                    Color::Monochrome => 'ic:gray',
                    default => 'ic:specific,isc:' . $this->color->value,
                };
            }

            if ($this->minWidth || $this->minHeight) {
                $parts[] = 'isz:lt,islt:' . $this->largerThanBucket();
            } elseif ($this->size) {
                $bucket = match ($this->size) {
                    ImageSize::Icon => 'i',
                    ImageSize::Small => null,
                    ImageSize::Medium => 'm',
                    ImageSize::Large, ImageSize::Wallpaper => 'l',
                };

                if ($bucket) {
                    $parts[] = 'isz:' . $bucket;
                }
            }

            if ($layout = $this->firstSupportedLayout(['square' => 's', 'tall' => 't', 'wide' => 'w', 'panoramic' => 'xw'])) {
                $parts[] = 'iar:' . $layout;
            }
        }

        if ($this->type === SearchType::Videos && $this->duration) {
            $parts[] = 'dur:' . $this->duration->value[0];
        }

        return $parts;
    }

    /**
     * Google's "larger than" buckets, by pixel count.
     */
    protected function largerThanBucket(): string
    {
        $pixels = ($this->minWidth ?? 1) * ($this->minHeight ?? 1);

        if ($this->minHeight === null) {
            $pixels = (int) (($this->minWidth ?? 0) * ($this->minWidth ?? 0) * 0.75);
        }

        return match (true) {
            $pixels <= 400 * 300 => 'qsvga',
            $pixels <= 640 * 480 => 'vga',
            $pixels <= 800 * 600 => 'svga',
            $pixels <= 1024 * 768 => 'xga',
            $pixels <= 2_000_000 => '2mp',
            $pixels <= 4_000_000 => '4mp',
            $pixels <= 6_000_000 => '6mp',
            $pixels <= 8_000_000 => '8mp',
            $pixels <= 10_000_000 => '10mp',
            $pixels <= 12_000_000 => '12mp',
            $pixels <= 15_000_000 => '15mp',
            $pixels <= 20_000_000 => '20mp',
            $pixels <= 40_000_000 => '40mp',
            default => '70mp',
        };
    }
}
