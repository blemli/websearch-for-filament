<?php

namespace Blemli\WebSearch\Actions;

use Blemli\WebSearch\Engines\Engine;
use Blemli\WebSearch\Enums\Color;
use Blemli\WebSearch\Enums\Country;
use Blemli\WebSearch\Enums\Duration;
use Blemli\WebSearch\Enums\ImageSize;
use Blemli\WebSearch\Enums\Layout;
use Blemli\WebSearch\Enums\License;
use Blemli\WebSearch\Enums\OpenIn;
use Blemli\WebSearch\Enums\SearchType;
use Blemli\WebSearch\Enums\Timespan;
use Blemli\WebSearch\Events\SearchOpened;
use Blemli\WebSearch\Support\SearchOptions;
use Blemli\WebSearch\WebSearch;
use Closure;
use DateTimeInterface;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use UnitEnum;

/**
 * Opens a search for the field's value (or any query you build) on the
 * user's engine. Works as a hint action, a table row action, a header
 * action — anywhere a Filament action goes.
 */
class WebSearchAction extends Action
{
    /**
     * @var class-string<Engine> | string | Closure | null
     */
    protected string | Closure | null $engine = null;

    /**
     * @var string | array<string> | Closure | null
     */
    protected string | array | Closure | null $query = null;

    protected SearchType | Closure $type = SearchType::Web;

    protected SearchOptions $options;

    protected OpenIn | Closure | null $openIn = null;

    protected int | Closure $popupWidth = 1200;

    protected int | Closure $popupHeight = 800;

    protected bool | Closure | null $track = null;

    public static function getDefaultName(): ?string
    {
        return 'websearch';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->options = new SearchOptions;

        $this->label(fn (): string => __(
            "websearch-for-filament::websearch.action.{$this->getType()->value}",
            ['engine' => $this->getEngine()::name()],
        ));
        $this->icon(fn (): string => $this->getEngine()::icon());
        $this->url(fn (): ?string => $this->opensInSlideOver() ? null : $this->getHref());
        $this->openUrlInNewTab(fn (): bool => $this->getOpenIn() === OpenIn::NewTab);
        $this->extraAttributes(fn (): array => $this->getOpenIn() === OpenIn::Popup
            ? ['onclick' => sprintf(
                "window.open(this.href, 'websearch', 'popup,width=%d,height=%d'); return false;",
                $this->evaluate($this->popupWidth),
                $this->evaluate($this->popupHeight),
            )]
            : []);
        $this->modal(fn (): bool => $this->opensInSlideOver());
        $this->slideOver(fn (): bool => $this->opensInSlideOver());
        $this->modalWidth(Width::SevenExtraLarge);
        $this->modalSubmitAction(false);
        $this->modalCancelAction(false);
        $this->modalContent(fn (): ?View => $this->opensInSlideOver()
            ? view('websearch-for-filament::embed', ['url' => $this->getSearchUrl()])
            : null);
        $this->mountUsing(function (): void {
            if ($this->shouldTrack()) {
                $search = $this->getSearch();

                SearchOpened::dispatch(Filament::auth()->user(), $search::class, $search->getType(), $search->getQuery(), $search->url());
            }
        });
        $this->visible(fn (): bool => filled($this->getQueryString()) && app(WebSearch::class)->isAuthorized());
    }

    /**
     * @param  class-string<Engine> | string | Closure | null  $engine  Engine class or key ("duckduckgo"); null = the user's choice
     */
    public function engine(string | Closure | null $engine): static
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * What to search for: a field name, several field names joined by a
     * space, or a closure (receives $get, $record, $state). Null searches
     * the value of the field the action is attached to.
     *
     * @param  string | array<string> | Closure | null  $query
     */
    public function query(string | array | Closure | null $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function type(SearchType | Closure $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function web(): static
    {
        return $this->type(SearchType::Web);
    }

    public function images(): static
    {
        return $this->type(SearchType::Images);
    }

    public function videos(): static
    {
        return $this->type(SearchType::Videos);
    }

    public function news(): static
    {
        return $this->type(SearchType::News);
    }

    public function shopping(): static
    {
        return $this->type(SearchType::Shopping);
    }

    public function license(?License $license): static
    {
        $this->options->license = $license;

        return $this;
    }

    public function imageColor(?Color $color): static
    {
        $this->options->color = $color;

        return $this;
    }

    public function transparent(): static
    {
        return $this->imageColor(Color::Transparent);
    }

    public function imageSize(?ImageSize $size): static
    {
        $this->options->size = $size;

        return $this;
    }

    public function largerThan(?int $width, ?int $height = null): static
    {
        $this->options->minWidth = $width;
        $this->options->minHeight = $height;

        return $this;
    }

    public function layout(?Layout ...$layouts): static
    {
        $this->options->layouts = array_values(array_filter($layouts));

        return $this;
    }

    public function duration(?Duration $duration): static
    {
        $this->options->duration = $duration;

        return $this;
    }

    /**
     * Without an argument the country comes from the app (locale region,
     * then timezone).
     */
    public function country(Country | bool | null $country = true): static
    {
        $this->options->country = $country;

        return $this;
    }

    public function since(DateTimeInterface | string | Closure | null $from): static
    {
        $this->options->since = $from instanceof Closure ? $this->evaluate($from) : $from;

        return $this;
    }

    public function until(DateTimeInterface | string | Closure | null $until): static
    {
        $this->options->until = $until instanceof Closure ? $this->evaluate($until) : $until;

        return $this;
    }

    public function between(DateTimeInterface | string | Closure | null $from, DateTimeInterface | string | Closure | null $until): static
    {
        return $this->since($from)->until($until);
    }

    public function within(?Timespan $timespan): static
    {
        $this->options->timespan = $timespan;

        return $this;
    }

    public function openIn(OpenIn | Closure | null $openIn): static
    {
        $this->openIn = $openIn;

        return $this;
    }

    public function openInNewTab(): static
    {
        return $this->openIn(OpenIn::NewTab);
    }

    public function openInSameTab(): static
    {
        return $this->openIn(OpenIn::SameTab);
    }

    public function openInPopup(int | Closure $width = 1200, int | Closure $height = 800): static
    {
        $this->popupWidth = $width;
        $this->popupHeight = $height;

        return $this->openIn(OpenIn::Popup);
    }

    /**
     * Show the results inside the panel. Engines that refuse to be framed
     * open in a new tab instead.
     */
    public function openInSlideOver(): static
    {
        return $this->openIn(OpenIn::SlideOver);
    }

    /**
     * Fire SearchOpened on every click (through a signed redirect).
     */
    public function track(bool | Closure $track = true): static
    {
        $this->track = $track;

        return $this;
    }

    /**
     * @return class-string<Engine>
     */
    public function getEngine(): string
    {
        $manager = app(WebSearch::class);
        $engine = $this->evaluate($this->engine);

        return $engine ? $manager->resolveEngine($engine) : $manager->engineFor();
    }

    public function getType(): SearchType
    {
        return $this->evaluate($this->type);
    }

    public function getOptions(): SearchOptions
    {
        return $this->options;
    }

    public function getOpenIn(): OpenIn
    {
        return $this->evaluate($this->openIn) ?? app(WebSearch::class)->openInFor();
    }

    public function shouldTrack(): bool
    {
        return $this->evaluate($this->track) ?? app(WebSearch::class)->isTrackingEnabled();
    }

    public function opensInSlideOver(): bool
    {
        return $this->getOpenIn() === OpenIn::SlideOver && $this->getEngine()::canBeEmbedded();
    }

    /**
     * The words that will be searched, or null when there is nothing to
     * search for (the action hides itself then).
     */
    public function getQueryString(): ?string
    {
        if ($this->query === null) {
            $value = $this->getSchemaComponent() ? $this->getSchemaComponentState() : null;

            return $this->stringify($value) ?: null;
        }

        if ($this->query instanceof Closure) {
            return $this->stringify($this->evaluate($this->query)) ?: null;
        }

        $get = $this->getSchemaComponent()?->makeGetUtility();
        $record = $this->getRecord();

        $words = array_map(
            fn (string $name): string => $this->stringify($get ? $get($name) : data_get($record, $name)),
            Arr::wrap($this->query),
        );

        return trim(implode(' ', array_filter($words))) ?: null;
    }

    /**
     * The configured engine, or its web search when it cannot do the type.
     */
    public function getSearch(): Engine
    {
        $engine = $this->getEngine();
        $type = $engine::supports($this->getType()) ? $this->getType() : SearchType::Web;
        $options = clone $this->options;
        $options->country ??= app(WebSearch::class)->countryFor();

        return $engine::for($type, (string) $this->getQueryString())->withOptions($options);
    }

    public function getSearchUrl(): string
    {
        return $this->getSearch()->url();
    }

    /**
     * The link target: the engine URL, or the signed tracking redirect.
     */
    public function getHref(): string
    {
        $search = $this->getSearch();
        $panel = Filament::getCurrentPanel();

        if (! $this->shouldTrack() || ! $panel?->hasPlugin('websearch-for-filament')) {
            return $search->url();
        }

        return URL::signedRoute($panel->generateRouteName('websearch.open'), [
            'engine' => $search::key(),
            'type' => $search->getType()->value,
            'q' => $search->getQuery(),
            'url' => $search->url(),
        ]);
    }

    protected function stringify(mixed $value): string
    {
        if (is_array($value)) {
            return trim(implode(' ', array_filter(array_map(fn (mixed $item): string => $this->stringify($item), $value))));
        }

        if ($value instanceof UnitEnum) {
            return (string) ($value->value ?? $value->name);
        }

        if ($value === null || is_bool($value) || (! is_scalar($value) && ! $value instanceof \Stringable)) {
            return '';
        }

        return trim((string) $value);
    }
}
