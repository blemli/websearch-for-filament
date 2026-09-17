<?php

namespace Blemli\WebSearch\Engines\Concerns;

use Blemli\WebSearch\Enums\Color;

trait FiltersByColor
{
    protected ?Color $color = null;

    public function color(?Color $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function transparent(): static
    {
        return $this->color(Color::Transparent);
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }
}
