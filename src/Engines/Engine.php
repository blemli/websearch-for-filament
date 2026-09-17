<?php

namespace Blemli\WebSearch\Engines;

use Blemli\WebSearch\Enums\SearchType;
use Blemli\WebSearch\Support\SearchOptions;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Stringable;

/**
 * A search engine: static constructors per supported search type (web(),
 * images(), …) and fluent filters per capability, ending in url(). Each
 * engine only exposes the methods it can actually express in a URL.
 */
abstract class Engine implements Stringable
{
    protected string $query;

    protected SearchType $type;

    final public function __construct() {}

    final protected static function make(SearchType $type, string $query): static
    {
        $engine = new static;
        $engine->type = $type;
        $engine->query = $query;

        return $engine;
    }

    /**
     * Build a search of any supported type, e.g. from a user's preference.
     */
    public static function for(SearchType $type, string $query): static
    {
        if (! static::supports($type)) {
            throw new InvalidArgumentException(static::name() . " does not support {$type->value} searches.");
        }

        return static::make($type, $query);
    }

    /**
     * The engine's own URL for the query.
     */
    abstract public function url(): string;

    /**
     * Short identifier, stored in preferences and used in icon names.
     */
    public static function key(): string
    {
        return Str::kebab(class_basename(static::class));
    }

    /**
     * Human-readable name for labels.
     */
    public static function name(): string
    {
        return class_basename(static::class);
    }

    /**
     * The glyph to show for this engine. Hosts override it through the
     * Filament icon alias "websearch::engine.<key>".
     */
    public static function icon(): string
    {
        return FilamentIcon::resolve('websearch::engine.' . static::key()) ?: static::defaultIcon();
    }

    /**
     * The bundled glyph when there is one, else a plain magnifier.
     */
    protected static function defaultIcon(): string
    {
        return file_exists(__DIR__ . '/../../resources/svg/' . static::key() . '.svg')
            ? 'websearch-' . static::key()
            : 'websearch-search';
    }

    /**
     * Whether the engine's result pages may be shown inside an iframe.
     */
    public static function canBeEmbedded(): bool
    {
        return false;
    }

    /**
     * @return list<SearchType>
     */
    public static function supportedTypes(): array
    {
        return array_values(array_filter(
            SearchType::cases(),
            fn (SearchType $type): bool => method_exists(static::class, $type->value),
        ));
    }

    public static function supports(SearchType $type): bool
    {
        return method_exists(static::class, $type->value);
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getType(): SearchType
    {
        return $this->type;
    }

    public function withOptions(SearchOptions $options): static
    {
        $options->applyTo($this);

        return $this;
    }

    public function __toString(): string
    {
        return $this->url();
    }

    /**
     * @param  array<string, string | int | null>  $params
     */
    protected function buildUrl(string $base, array $params): string
    {
        $params = array_filter($params, fn (string | int | null $value): bool => $value !== null && $value !== '');

        return $params === [] ? $base : $base . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Two-letter language of the app locale ("de_CH" → "de").
     */
    protected function appLanguage(string $default = 'en'): string
    {
        $language = strtolower(substr(str_replace('-', '_', app()->getLocale()), 0, 2));

        return strlen($language) === 2 ? $language : $default;
    }
}
