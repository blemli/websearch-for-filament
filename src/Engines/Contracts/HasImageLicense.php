<?php

namespace Blemli\WebSearch\Engines\Contracts;

use Blemli\WebSearch\Enums\License;

interface HasImageLicense
{
    public function license(?License $license): static;
}
