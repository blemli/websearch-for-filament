<?php

namespace Blemli\WebSearch\Engines\Concerns;

use Blemli\WebSearch\Enums\Country;

trait FiltersByCountry
{
    protected ?Country $country = null;

    public function country(Country | null | bool $country = true): static
    {
        $this->country = match (true) {
            $country === true => Country::fromApp(),
            $country === false => null,
            default => $country,
        };

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }
}
