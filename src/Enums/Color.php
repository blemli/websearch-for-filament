<?php

namespace Blemli\WebSearch\Enums;

enum Color: string
{
    case Transparent = 'transparent';
    case FullColor = 'color';
    case Monochrome = 'monochrome';
    case Red = 'red';
    case Orange = 'orange';
    case Yellow = 'yellow';
    case Green = 'green';
    case Teal = 'teal';
    case Blue = 'blue';
    case Purple = 'purple';
    case Pink = 'pink';
    case White = 'white';
    case Gray = 'gray';
    case Black = 'black';
    case Brown = 'brown';

    public function isSpecific(): bool
    {
        return ! in_array($this, [self::Transparent, self::FullColor, self::Monochrome], true);
    }
}
