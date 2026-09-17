<?php

namespace Blemli\WebSearch\Engines\Contracts;

use Blemli\WebSearch\Enums\Color;

interface HasImageColor
{
    public function color(?Color $color): static;

    public function transparent(): static;
}
