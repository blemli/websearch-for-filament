<?php

namespace Blemli\WebSearch\Engines\Contracts;

use Blemli\WebSearch\Enums\Country;

interface HasCountry
{
    /**
     * Restrict results to a country. Without an argument the country is
     * derived from the app (locale region, then timezone); pass null to
     * search worldwide again.
     */
    public function country(Country | null | bool $country = true): static;
}
