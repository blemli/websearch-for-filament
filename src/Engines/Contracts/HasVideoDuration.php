<?php

namespace Blemli\WebSearch\Engines\Contracts;

use Blemli\WebSearch\Enums\Duration;

interface HasVideoDuration
{
    public function duration(?Duration $duration): static;
}
