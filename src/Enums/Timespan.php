<?php

namespace Blemli\WebSearch\Enums;

enum Timespan: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';
}
