<?php

namespace Blemli\WebSearch\Support;

use Blemli\WebSearch\Engines\Contracts\HasCountry;
use Blemli\WebSearch\Engines\Contracts\HasImageColor;
use Blemli\WebSearch\Engines\Contracts\HasImageLayout;
use Blemli\WebSearch\Engines\Contracts\HasImageLicense;
use Blemli\WebSearch\Engines\Contracts\HasImageSize;
use Blemli\WebSearch\Engines\Contracts\HasTimespan;
use Blemli\WebSearch\Engines\Contracts\HasVideoDuration;
use Blemli\WebSearch\Engines\Engine;
use Blemli\WebSearch\Enums\Color;
use Blemli\WebSearch\Enums\Country;
use Blemli\WebSearch\Enums\Duration;
use Blemli\WebSearch\Enums\ImageSize;
use Blemli\WebSearch\Enums\Layout;
use Blemli\WebSearch\Enums\License;
use Blemli\WebSearch\Enums\Timespan;
use DateTimeInterface;

/**
 * Engine-independent filter set. Applied to an engine, every option the
 * engine can express is set and the rest is dropped silently.
 */
class SearchOptions
{
    public ?License $license = null;

    public ?Color $color = null;

    public ?ImageSize $size = null;

    public ?int $minWidth = null;

    public ?int $minHeight = null;

    /**
     * @var list<Layout>
     */
    public array $layouts = [];

    public ?Duration $duration = null;

    /**
     * true = derive from the app when applied.
     */
    public Country | bool | null $country = null;

    public DateTimeInterface | string | null $since = null;

    public DateTimeInterface | string | null $until = null;

    public ?Timespan $timespan = null;

    public static function make(): self
    {
        return new self;
    }

    public function applyTo(Engine $engine): Engine
    {
        if ($engine instanceof HasImageLicense && $this->license !== null) {
            $engine->license($this->license);
        }

        if ($engine instanceof HasImageColor && $this->color !== null) {
            $engine->color($this->color);
        }

        if ($engine instanceof HasImageSize) {
            if ($this->size !== null) {
                $engine->size($this->size);
            }

            if ($this->minWidth !== null || $this->minHeight !== null) {
                $engine->largerThan($this->minWidth, $this->minHeight);
            }
        }

        if ($engine instanceof HasImageLayout && $this->layouts !== []) {
            $engine->layout(...$this->layouts);
        }

        if ($engine instanceof HasVideoDuration && $this->duration !== null) {
            $engine->duration($this->duration);
        }

        if ($engine instanceof HasCountry && $this->country !== null) {
            $engine->country($this->country);
        }

        if ($engine instanceof HasTimespan) {
            if ($this->since !== null || $this->until !== null) {
                $engine->between($this->since, $this->until);
            }

            if ($this->timespan !== null) {
                $engine->within($this->timespan);
            }
        }

        return $engine;
    }
}
