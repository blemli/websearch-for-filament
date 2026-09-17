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
use Blemli\WebSearch\Enums\Country;
use Blemli\WebSearch\Enums\License;
use Blemli\WebSearch\Enums\SearchType;

class DuckDuckGo extends Engine implements HasCountry, HasImageColor, HasImageLayout, HasImageLicense, HasImageSize, HasTimespan, HasVideoDuration
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

    public static function key(): string
    {
        return 'duckduckgo';
    }

    public function url(): string
    {
        $filters = $this->iaf();

        return $this->buildUrl('https://duckduckgo.com/', [
            'q' => $this->query,
            'ia' => $this->type === SearchType::Web ? null : $this->type->value,
            'iax' => in_array($this->type, [SearchType::Images, SearchType::Videos, SearchType::Shopping], true) ? $this->type->value : null,
            'iar' => $this->type === SearchType::News ? 'news' : null,
            'iaf' => $filters === [] ? null : implode(',', $filters),
            'df' => $this->df(),
            'kl' => $this->country ? $this->region($this->country) : null,
        ]);
    }

    protected function df(): ?string
    {
        if ($this->hasDateRange()) {
            return ($this->since?->format('Y-m-d') ?? '') . '..' . ($this->until?->format('Y-m-d') ?? '');
        }

        return $this->timespan?->value[0];
    }

    /**
     * DuckDuckGo regions are "country-language", with the UK as "uk".
     */
    protected function region(Country $country): string
    {
        $code = $country === Country::GB ? 'uk' : $country->value;

        return $code . '-' . $this->appLanguage();
    }

    /**
     * @return list<string>
     */
    protected function iaf(): array
    {
        $parts = [];

        if ($this->type === SearchType::Images) {
            if ($this->size) {
                $parts[] = 'size:' . match ($this->size->value) {
                    'icon', 'small' => 'Small',
                    default => ucfirst($this->size->value),
                };
            }

            if ($this->color) {
                $parts[] = match ($this->color) {
                    Color::Transparent => 'type:transparent',
                    Color::FullColor => 'color:color',
                    Color::Monochrome => 'color:Monochrome',
                    default => 'color:' . ucfirst($this->color->value),
                };
            }

            if ($layout = $this->firstSupportedLayout(['square' => 'Square', 'tall' => 'Tall', 'wide' => 'Wide', 'panoramic' => 'Wide'])) {
                $parts[] = 'layout:' . $layout;
            }

            if ($this->license) {
                $parts[] = 'license:' . match ($this->license) {
                    License::PublicDomain => 'Public',
                    License::CreativeCommons => 'Share',
                    License::Commercial => 'ShareCommercially',
                    License::Modify => 'Modify',
                    License::ModifyCommercially => 'ModifyCommercially',
                    License::Any => 'Any',
                };
            }
        }

        if ($this->type === SearchType::Videos && $this->duration) {
            $parts[] = 'videoDuration:' . $this->duration->value;
        }

        return $parts;
    }
}
