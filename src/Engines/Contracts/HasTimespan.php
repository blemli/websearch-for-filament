<?php

namespace Blemli\WebSearch\Engines\Contracts;

use Blemli\WebSearch\Enums\Timespan;
use DateTimeInterface;

interface HasTimespan
{
    public function since(DateTimeInterface | string | null $from): static;

    public function until(DateTimeInterface | string | null $until): static;

    public function between(DateTimeInterface | string | null $from, DateTimeInterface | string | null $until): static;

    public function within(?Timespan $timespan): static;
}
