<?php

namespace Blemli\WebSearch\Engines;

use Blemli\WebSearch\Engines\Concerns\FiltersByColor;
use Blemli\WebSearch\Engines\Concerns\FiltersByDuration;
use Blemli\WebSearch\Engines\Concerns\FiltersByLayout;
use Blemli\WebSearch\Engines\Concerns\FiltersBySize;
use Blemli\WebSearch\Engines\Concerns\SearchesImages;
use Blemli\WebSearch\Engines\Concerns\SearchesVideos;
use Blemli\WebSearch\Engines\Concerns\SearchesWeb;
use Blemli\WebSearch\Engines\Contracts\HasImageColor;
use Blemli\WebSearch\Engines\Contracts\HasImageLayout;
use Blemli\WebSearch\Engines\Contracts\HasImageSize;
use Blemli\WebSearch\Engines\Contracts\HasVideoDuration;
use Blemli\WebSearch\Enums\Color;
use Blemli\WebSearch\Enums\SearchType;

class Yandex extends Engine implements HasImageColor, HasImageLayout, HasImageSize, HasVideoDuration
{
    use FiltersByColor;
    use FiltersByDuration;
    use FiltersByLayout;
    use FiltersBySize;
    use SearchesImages;
    use SearchesVideos;
    use SearchesWeb;

    public function url(): string
    {
        return match ($this->type) {
            SearchType::Images => $this->buildUrl('https://yandex.com/images/search', [
                'text' => $this->query,
                'isize' => match ($this->size?->value) {
                    'icon', 'small' => 'small',
                    'medium' => 'medium',
                    'large', 'wallpaper' => 'large',
                    null => null,
                },
                'icolor' => match ($this->color) {
                    null, Color::Transparent => null,
                    Color::FullColor => 'color',
                    Color::Monochrome => 'gray',
                    Color::Teal => 'cyan',
                    Color::Purple => 'violet',
                    Color::Pink => 'red',
                    Color::Brown => 'orange',
                    default => $this->color->value,
                },
                'iorient' => $this->firstSupportedLayout(['square' => 'square', 'tall' => 'vertical', 'wide' => 'horizontal', 'panoramic' => 'horizontal']),
            ]),
            SearchType::Videos => $this->buildUrl('https://yandex.com/video/search', [
                'text' => $this->query,
                'duration' => $this->duration?->value,
            ]),
            default => $this->buildUrl('https://yandex.com/search/', ['text' => $this->query]),
        };
    }
}
