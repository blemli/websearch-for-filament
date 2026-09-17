<?php

namespace Blemli\WebSearch\Enums;

use Filament\Support\Contracts\HasLabel;

enum OpenIn: string implements HasLabel
{
    case NewTab = 'new_tab';
    case SameTab = 'same_tab';
    case Popup = 'popup';
    case SlideOver = 'slide_over';

    public function getLabel(): string
    {
        return __("websearch-for-filament::websearch.open_in.{$this->value}");
    }
}
