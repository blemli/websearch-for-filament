<?php

use Blemli\WebSearch\Engines\Baidu;
use Blemli\WebSearch\Engines\DuckDuckGo;
use Blemli\WebSearch\Engines\Ecosia;
use Blemli\WebSearch\Engines\Google;
use Blemli\WebSearch\Engines\Swisscows;
use Blemli\WebSearch\Enums\OpenIn;
use Blemli\WebSearch\Livewire\BreezySearchPreferences;
use Blemli\WebSearch\Livewire\SearchPreferences;
use Blemli\WebSearch\Models\Preference;
use Blemli\WebSearch\Stores\CookieStore;
use Blemli\WebSearch\WebSearch;
use Blemli\WebSearch\WebSearchPlugin;
use Illuminate\Support\Facades\Cookie;

use function Pest\Livewire\livewire;

beforeEach(function () {
    config()->set('websearch-for-filament.user_choice', true);
});

it('ignores preferences while user choice is off', function () {
    config()->set('websearch-for-filament.user_choice', false);
    request()->cookies->set(CookieStore::NAME, json_encode(['engine' => 'duckduckgo']));

    expect(app(WebSearch::class)->engineFor())->toBe(Google::class);
});

it('reads and writes the cookie store', function () {
    app(WebSearch::class)->savePreferences(['engine' => 'duckduckgo', 'open_in' => 'popup', 'country' => null]);

    $cookie = Cookie::queued(CookieStore::NAME);

    expect($cookie)->not->toBeNull()
        ->and(json_decode($cookie->getValue(), true))->toBe(['engine' => 'duckduckgo', 'open_in' => 'popup']);

    request()->cookies->set(CookieStore::NAME, $cookie->getValue());

    expect(app(WebSearch::class)->engineFor())->toBe(DuckDuckGo::class)
        ->and(app(WebSearch::class)->openInFor())->toBe(OpenIn::Popup);
});

it('stores preferences on a user attribute', function () {
    config()->set('websearch-for-filament.store', 'attribute');
    bootPanel();
    $user = loginUser();

    app(WebSearch::class)->savePreferences(['engine' => 'swisscows', 'country' => 'ch']);

    expect($user->fresh()->websearch_preferences)->toBe(['engine' => 'swisscows', 'country' => 'ch'])
        ->and(app(WebSearch::class)->engineFor($user->fresh()))->toBe(Swisscows::class)
        ->and(app(WebSearch::class)->countryFor($user->fresh())?->value)->toBe('ch');
});

it('stores preferences in the package table', function () {
    config()->set('websearch-for-filament.store', 'database');
    createPreferencesTable();
    bootPanel();
    $user = loginUser();

    app(WebSearch::class)->savePreferences(['engine' => 'ecosia', 'open_in' => 'same_tab']);
    app(WebSearch::class)->savePreferences(['engine' => 'ecosia', 'open_in' => 'slide_over']);

    expect(Preference::count())->toBe(1)
        ->and(app(WebSearch::class)->engineFor($user))->toBe(Ecosia::class)
        ->and(app(WebSearch::class)->openInFor($user))->toBe(OpenIn::SlideOver);
});

it('falls back to the default when the preferred engine is no longer allowed', function () {
    bootPanel()->plugin(WebSearchPlugin::make()->except([DuckDuckGo::class]));
    request()->cookies->set(CookieStore::NAME, json_encode(['engine' => 'duckduckgo']));

    expect(app(WebSearch::class)->engineFor())->toBe(Google::class);
});

it('offers only allowed engines', function () {
    bootPanel()->plugin(WebSearchPlugin::make()->engines([Google::class, Baidu::class])->except([Baidu::class]));

    expect(app(WebSearch::class)->engines())->toBe([Google::class]);
});

it('saves the form through the livewire component', function () {
    bootPanel();
    loginUser();

    livewire(SearchPreferences::class)
        ->assertFormSet(['engine' => 'google', 'open_in' => OpenIn::NewTab])
        ->fillForm(['engine' => 'duckduckgo', 'open_in' => 'slide_over', 'country' => 'ch'])
        ->call('submit')
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect(json_decode(Cookie::queued(CookieStore::NAME)->getValue(), true))
        ->toBe(['engine' => 'duckduckgo', 'open_in' => 'slide_over', 'country' => 'ch']);
});

it('saves through the breezy profile component, whose name livewire can resolve', function () {
    bootPanel();
    loginUser();

    livewire(BreezySearchPreferences::class)
        ->fillForm(['engine' => 'swisscows', 'open_in' => 'slide_over'])
        ->call('submit')
        ->assertHasNoFormErrors()
        ->assertNotified();

    // call('submit') above already had Livewire resolve the component by its
    // name — that is what breaks when the name does not match the alias.
    expect(json_decode(Cookie::queued(CookieStore::NAME)->getValue(), true))->toBe(['engine' => 'swisscows', 'open_in' => 'slide_over']);
});
