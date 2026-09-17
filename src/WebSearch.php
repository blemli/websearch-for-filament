<?php

namespace Blemli\WebSearch;

use BackedEnum;
use Blemli\WebSearch\Engines\Engine;
use Blemli\WebSearch\Engines\Google;
use Blemli\WebSearch\Enums\Country;
use Blemli\WebSearch\Enums\OpenIn;
use Blemli\WebSearch\Stores\AttributeStore;
use Blemli\WebSearch\Stores\CookieStore;
use Blemli\WebSearch\Stores\DatabaseStore;
use Blemli\WebSearch\Stores\PreferenceStore;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use InvalidArgumentException;

/**
 * Resolves engines and defaults from three layers: the action, the
 * user's preference, then the panel plugin, then the config file.
 */
class WebSearch
{
    public function plugin(): ?WebSearchPlugin
    {
        return rescue(fn (): ?WebSearchPlugin => WebSearchPlugin::current(), report: false);
    }

    /**
     * Engines users may choose from.
     *
     * @return list<class-string<Engine>>
     */
    public function engines(): array
    {
        $engines = $this->plugin()?->getEngines() ?? config('websearch-for-filament.engines', [Google::class]);
        $except = $this->plugin()?->getExcept() ?? [];

        return array_values(array_filter(
            $engines,
            fn (string $engine): bool => is_subclass_of($engine, Engine::class) && ! in_array($engine, $except, true),
        ));
    }

    /**
     * @return class-string<Engine>
     */
    public function defaultEngine(): string
    {
        $engine = $this->plugin()?->getDefaultEngine() ?? config('websearch-for-filament.default', Google::class);

        return $this->resolveEngine($engine);
    }

    /**
     * Accepts a class name or an engine key ("duckduckgo").
     *
     * @return class-string<Engine>
     */
    public function resolveEngine(string $engine): string
    {
        if (is_subclass_of($engine, Engine::class)) {
            return $engine;
        }

        foreach ([...$this->engines(), ...config('websearch-for-filament.engines', [])] as $candidate) {
            if (is_subclass_of($candidate, Engine::class) && $candidate::key() === $engine) {
                return $candidate;
            }
        }

        throw new InvalidArgumentException("Unknown search engine [{$engine}].");
    }

    /**
     * The user's engine when user choice is on and they picked one that is
     * still allowed, otherwise the default.
     *
     * @return class-string<Engine>
     */
    public function engineFor(?Authenticatable $user = null): string
    {
        $preferred = $this->preferencesFor($user)['engine'] ?? null;

        if ($preferred !== null) {
            $engine = rescue(fn (): string => $this->resolveEngine($preferred), report: false);

            if ($engine !== null && in_array($engine, $this->engines(), true)) {
                return $engine;
            }
        }

        return $this->defaultEngine();
    }

    public function openInFor(?Authenticatable $user = null): OpenIn
    {
        $preferred = $this->preferencesFor($user)['open_in'] ?? null;

        return ($preferred ? OpenIn::tryFrom($preferred) : null)
            ?? $this->plugin()?->getOpenIn()
            ?? OpenIn::tryFrom((string) config('websearch-for-filament.open_in', 'new_tab'))
            ?? OpenIn::NewTab;
    }

    public function countryFor(?Authenticatable $user = null): ?Country
    {
        $preferred = $this->preferencesFor($user)['country'] ?? null;

        return $preferred ? Country::tryFrom($preferred) : null;
    }

    /**
     * @return array{engine?: string | null, open_in?: string | null, country?: string | null}
     */
    public function preferencesFor(?Authenticatable $user = null): array
    {
        if (! $this->isUserChoiceEnabled()) {
            return [];
        }

        $user ??= rescue(fn (): ?Authenticatable => Filament::auth()->user(), report: false);

        return rescue(fn (): array => $this->store()->get($user), [], report: false);
    }

    /**
     * Enum values are accepted and stored as their backing strings.
     *
     * @param  array<string, BackedEnum | string | null>  $preferences
     */
    public function savePreferences(array $preferences, ?Authenticatable $user = null): void
    {
        $user ??= Filament::auth()->user();

        $normalized = [];

        foreach (['engine', 'open_in', 'country'] as $key) {
            $value = $preferences[$key] ?? null;
            $value = $value instanceof BackedEnum ? $value->value : $value;

            if (filled($value)) {
                $normalized[$key] = (string) $value;
            }
        }

        $this->store()->put($user, $normalized);
    }

    public function store(): PreferenceStore
    {
        $store = $this->plugin()?->getStore() ?? config('websearch-for-filament.store', 'cookie');

        if ($store instanceof PreferenceStore) {
            return $store;
        }

        return match ($store) {
            'cookie' => app(CookieStore::class),
            'attribute' => new AttributeStore((string) config('websearch-for-filament.attribute', 'websearch_preferences')),
            'database' => app(DatabaseStore::class),
            default => app($store),
        };
    }

    public function isUserChoiceEnabled(): bool
    {
        return $this->plugin()?->hasUserChoice() ?? (bool) config('websearch-for-filament.user_choice', false);
    }

    public function isTrackingEnabled(): bool
    {
        return $this->plugin()?->isTracking() ?? (bool) config('websearch-for-filament.track', false);
    }

    public function permission(): ?string
    {
        return $this->plugin()?->getPermission() ?? config('websearch-for-filament.permission');
    }

    public function isAuthorized(?Authenticatable $user = null): bool
    {
        $permission = $this->permission();

        if ($permission === null) {
            return true;
        }

        $user ??= Filament::auth()->user();

        return $user !== null && method_exists($user, 'can') && $user->can($permission);
    }
}
