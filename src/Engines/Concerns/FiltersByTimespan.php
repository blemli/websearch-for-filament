<?php

namespace Blemli\WebSearch\Engines\Concerns;

use Blemli\WebSearch\Enums\Timespan;
use Carbon\CarbonImmutable;
use DateTimeInterface;

trait FiltersByTimespan
{
    protected ?CarbonImmutable $since = null;

    protected ?CarbonImmutable $until = null;

    protected ?Timespan $timespan = null;

    public function since(DateTimeInterface | string | null $from): static
    {
        $this->since = $this->toDate($from);

        return $this;
    }

    public function until(DateTimeInterface | string | null $until): static
    {
        $this->until = $this->toDate($until);

        return $this;
    }

    public function between(DateTimeInterface | string | null $from, DateTimeInterface | string | null $until): static
    {
        return $this->since($from)->until($until);
    }

    public function within(?Timespan $timespan): static
    {
        $this->timespan = $timespan;

        return $this;
    }

    public function getSince(): ?CarbonImmutable
    {
        return $this->since;
    }

    public function getUntil(): ?CarbonImmutable
    {
        return $this->until;
    }

    public function getTimespan(): ?Timespan
    {
        return $this->timespan;
    }

    protected function hasDateRange(): bool
    {
        return $this->since !== null || $this->until !== null;
    }

    protected function toDate(DateTimeInterface | string | null $date): ?CarbonImmutable
    {
        if ($date === null || $date === '') {
            return null;
        }

        return CarbonImmutable::parse($date);
    }
}
