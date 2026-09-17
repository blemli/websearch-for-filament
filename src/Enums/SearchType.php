<?php

namespace Blemli\WebSearch\Enums;

use Filament\Support\Contracts\HasLabel;

enum SearchType: string implements HasLabel
{
    case Web = 'web';
    case Images = 'images';
    case Videos = 'videos';
    case News = 'news';
    case Shopping = 'shopping';

    public function getLabel(): string
    {
        return __("websearch-for-filament::websearch.types.{$this->value}");
    }
}
