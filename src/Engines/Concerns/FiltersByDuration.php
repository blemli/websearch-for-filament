<?php

namespace Blemli\WebSearch\Engines\Concerns;

use Blemli\WebSearch\Enums\Duration;

trait FiltersByDuration
{
    protected ?Duration $duration = null;

    public function duration(?Duration $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDuration(): ?Duration
    {
        return $this->duration;
    }
}
