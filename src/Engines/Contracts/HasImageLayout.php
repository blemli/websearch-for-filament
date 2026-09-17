<?php

namespace Blemli\WebSearch\Engines\Contracts;

use Blemli\WebSearch\Enums\Layout;

interface HasImageLayout
{
    /**
     * Engines accept a single layout; when several are given, the first
     * one the engine understands wins.
     */
    public function layout(?Layout ...$layouts): static;
}
