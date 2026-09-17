<?php

namespace Blemli\WebSearch\Engines\Concerns;

use Blemli\WebSearch\Enums\License;

trait FiltersByLicense
{
    protected ?License $license = null;

    public function license(?License $license): static
    {
        $this->license = $license === License::Any ? null : $license;

        return $this;
    }

    public function getLicense(): ?License
    {
        return $this->license;
    }
}
