<?php

namespace Blemli\WebSearch\Engines\Contracts;

use Blemli\WebSearch\Enums\ImageSize;

interface HasImageSize
{
    public function size(?ImageSize $size): static;

    public function largerThan(?int $width, ?int $height = null): static;
}
