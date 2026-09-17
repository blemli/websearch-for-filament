<?php

namespace Blemli\WebSearch\Engines\Concerns;

use Blemli\WebSearch\Enums\ImageSize;

trait FiltersBySize
{
    protected ?ImageSize $size = null;

    protected ?int $minWidth = null;

    protected ?int $minHeight = null;

    public function size(?ImageSize $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Only images at least this large. A missing height means "at least
     * this wide".
     */
    public function largerThan(?int $width, ?int $height = null): static
    {
        $this->minWidth = $width;
        $this->minHeight = $height;

        return $this;
    }

    public function getSize(): ?ImageSize
    {
        return $this->size;
    }

    public function getMinWidth(): ?int
    {
        return $this->minWidth;
    }

    public function getMinHeight(): ?int
    {
        return $this->minHeight;
    }
}
