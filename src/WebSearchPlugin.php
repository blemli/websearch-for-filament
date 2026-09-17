<?php

namespace Blemli\WebSearch;

use Blemli\WebSearch\Engines\Engine;
use Blemli\WebSearch\Enums\OpenIn;
use Blemli\WebSearch\Http\Controllers\OpenSearchController;
use Blemli\WebSearch\Livewire\BreezySearchPreferences;
use Blemli\WebSearch\Stores\PreferenceStore;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Support\Facades\Route;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class WebSearchPlugin implements Plugin
{
    use EvaluatesClosures;

    /**
     * @var list<class-string<Engine>> | Closure | null
     */
    protected array | Closure | null $engines = null;

    /**
     * @var list<class-string<Engine>> | Closure | null
     */
    protected array | Closure | null $except = null;

    /**
     * @var class-string<Engine> | Closure | null
     */
    protected string | Closure | null $defaultEngine = null;

    protected OpenIn | Closure | null $openIn = null;

    /**
     * @var list<OpenIn> | Closure | null
     */
    protected array | Closure | null $openModes = null;

    /**
     * @var list<OpenIn> | Closure | null
     */
    protected array | Closure | null $exceptOpenModes = null;

    protected bool | Closure | null $userChoice = null;

    protected bool | Closure | null $track = null;

    protected string | Closure | null $permission = null;

    protected bool | Closure | null $profileComponent = null;

    protected PreferenceStore | string | Closure | null $store = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function current(): ?static
    {
        return rescue(fn (): ?Plugin => Filament::getCurrentPanel()?->getPlugin('websearch-for-filament'), report: false); // @phpstan-ignore return.type
    }

    public function getId(): string
    {
        return 'websearch-for-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->routes(function (): void {
            Route::get('/websearch/open', OpenSearchController::class)
                ->middleware('signed')
                ->name('websearch.open');
        });
    }

    public function boot(Panel $panel): void
    {
        if (! $this->hasProfileComponent()) {
            return;
        }

        $breezy = rescue(fn (): Plugin => $panel->getPlugin('filament-breezy'), report: false);

        if ($breezy instanceof BreezyCore) {
            $breezy->myProfileComponents(['websearch_preferences' => BreezySearchPreferences::class]);
        }
    }

    /**
     * Engines users may choose from (allow list).
     *
     * @param  list<class-string<Engine>> | Closure  $engines
     */
    public function engines(array | Closure $engines): static
    {
        $this->engines = $engines;

        return $this;
    }

    /**
     * @return list<class-string<Engine>> | null
     */
    public function getEngines(): ?array
    {
        return $this->evaluate($this->engines);
    }

    /**
     * Engines users may not choose (deny list).
     *
     * @param  list<class-string<Engine>> | Closure  $engines
     */
    public function except(array | Closure $engines): static
    {
        $this->except = $engines;

        return $this;
    }

    /**
     * @return list<class-string<Engine>>
     */
    public function getExcept(): array
    {
        return $this->evaluate($this->except) ?? [];
    }

    /**
     * @param  class-string<Engine> | Closure  $engine
     */
    public function defaultEngine(string | Closure $engine): static
    {
        $this->defaultEngine = $engine;

        return $this;
    }

    /**
     * @return class-string<Engine> | null
     */
    public function getDefaultEngine(): ?string
    {
        return $this->evaluate($this->defaultEngine);
    }

    public function openIn(OpenIn | Closure $openIn): static
    {
        $this->openIn = $openIn;

        return $this;
    }

    public function getOpenIn(): ?OpenIn
    {
        return $this->evaluate($this->openIn);
    }

    /**
     * The open modes users may choose from (allow list).
     *
     * @param  list<OpenIn> | Closure  $modes
     */
    public function openModes(array | Closure $modes): static
    {
        $this->openModes = $modes;

        return $this;
    }

    /**
     * @return list<OpenIn> | null
     */
    public function getOpenModes(): ?array
    {
        return $this->evaluate($this->openModes);
    }

    /**
     * Open modes users may not choose (deny list).
     *
     * @param  list<OpenIn> | Closure  $modes
     */
    public function exceptOpenModes(array | Closure $modes): static
    {
        $this->exceptOpenModes = $modes;

        return $this;
    }

    /**
     * @return list<OpenIn>
     */
    public function getExceptOpenModes(): array
    {
        return $this->evaluate($this->exceptOpenModes) ?? [];
    }

    /**
     * Let users pick their engine in their profile.
     */
    public function userChoice(bool | Closure $enabled = true): static
    {
        $this->userChoice = $enabled;

        return $this;
    }

    public function hasUserChoice(): ?bool
    {
        return $this->evaluate($this->userChoice);
    }

    /**
     * Fire SearchOpened (and log to activitylog when installed) on every
     * click, via a signed redirect.
     */
    public function trackSearches(bool | Closure $enabled = true): static
    {
        $this->track = $enabled;

        return $this;
    }

    public function isTracking(): ?bool
    {
        return $this->evaluate($this->track);
    }

    /**
     * Gate ability required to see search actions.
     */
    public function permission(string | Closure | null $ability): static
    {
        $this->permission = $ability;

        return $this;
    }

    public function getPermission(): ?string
    {
        return $this->evaluate($this->permission);
    }

    /**
     * Where user preferences live: 'cookie' (default, no migration),
     * 'attribute' (a JSON column on the user), 'database' (the package
     * table), a store class name or instance.
     */
    public function store(PreferenceStore | string | Closure $store): static
    {
        $this->store = $store;

        return $this;
    }

    public function getStore(): PreferenceStore | string | null
    {
        return $this->evaluate($this->store);
    }

    /**
     * Add the preferences section to Filament Breezy's "My Profile" page.
     * Defaults to on whenever user choice is on and Breezy is installed.
     */
    public function profileComponent(bool | Closure $enabled = true): static
    {
        $this->profileComponent = $enabled;

        return $this;
    }

    public function hasProfileComponent(): bool
    {
        return $this->evaluate($this->profileComponent)
            ?? (app(WebSearch::class)->isUserChoiceEnabled() && class_exists(BreezyCore::class));
    }
}
