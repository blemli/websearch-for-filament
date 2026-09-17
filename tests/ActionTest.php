<?php

use Blemli\WebSearch\Actions\WebSearchAction;
use Blemli\WebSearch\Engines\Bing;
use Blemli\WebSearch\Engines\DuckDuckGo;
use Blemli\WebSearch\Engines\Google;
use Blemli\WebSearch\Engines\Swisscows;
use Blemli\WebSearch\Enums\Country;
use Blemli\WebSearch\Enums\OpenIn;
use Blemli\WebSearch\Events\SearchOpened;
use Blemli\WebSearch\Tests\Fixtures\TestForm;
use Blemli\WebSearch\WebSearchPlugin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;

use function Pest\Livewire\livewire;

function hintAction(TestForm $component, string $field): WebSearchAction
{
    $action = $component->getSchema('form')->getFlatFields()[$field]->getHintActions()[0];

    expect($action)->toBeInstanceOf(WebSearchAction::class);

    return $action;
}

it('searches the field value with the default engine and a translated label', function () {
    $component = livewire(TestForm::class)->fillForm(['name' => 'Fujifilm GFX'])->instance();
    $action = hintAction($component, 'name');

    expect($action->getLabel())->toBe('Search on Google')
        ->and($action->getIcon())->toBe('websearch-google')
        ->and($action->getUrl())->toBe('https://www.google.com/search?q=Fujifilm%20GFX')
        ->and($action->shouldOpenUrlInNewTab())->toBeTrue()
        ->and($action->isVisible())->toBeTrue();
});

it('hides itself while there is nothing to search for', function () {
    $component = livewire(TestForm::class)->instance();

    expect(hintAction($component, 'name')->isVisible())->toBeFalse()
        ->and(hintAction($component, 'image')->isVisible())->toBeFalse();
});

it('joins several fields for an image search and applies the configurator', function () {
    $component = livewire(TestForm::class)->fillForm(['name' => 'GFX 100S', 'vendor' => 'Fujifilm'])->instance();
    $action = hintAction($component, 'image');

    expect($action->getLabel())->toBe('Search images on DuckDuckGo')
        ->and($action->getUrl())->toBe('https://duckduckgo.com/?q=Fujifilm%20GFX%20100S&ia=images&iax=images&iaf=type%3Atransparent');
});

it('accepts a query closure with $get', function () {
    $component = livewire(TestForm::class)->fillForm(['name' => 'X100V', 'vendor' => 'Fujifilm'])->instance();

    expect(hintAction($component, 'manual')->getUrl())->toBe('https://www.google.com/search?q=Fujifilm%20X100V%20manual');
});

it('translates the label to german', function () {
    app()->setLocale('de');
    $component = livewire(TestForm::class)->fillForm(['name' => 'X', 'vendor' => 'Y'])->instance();

    expect(hintAction($component, 'image')->getLabel())->toBe('Bilder auf DuckDuckGo suchen');
});

it('works standalone with a record and dotted field names', function () {
    $record = new class extends Model
    {
        protected $guarded = [];
    };
    $record->setRelation('vendor', new $record(['name' => 'Leica']));
    $record->name = 'M11';

    $action = WebSearchAction::make()->record($record)->query(['vendor.name', 'name'])->engine('bing')->videos();

    expect($action->getUrl())->toBe('https://www.bing.com/videos/search?q=Leica%20M11');
});

it('falls back to a web search when the engine cannot do the type', function () {
    $action = WebSearchAction::make()->query(fn (): string => 'x')->engine(Swisscows::class)->shopping();

    expect($action->getUrl())->toBe('https://swisscows.com/en/web?query=x')
        ->and($action->getLabel())->toBe('Search shopping on Swisscows');
});

it('opens in the same tab, a popup or a slide-over', function () {
    $same = WebSearchAction::make()->query(fn (): string => 'x')->openInSameTab();
    $popup = WebSearchAction::make()->query(fn (): string => 'x')->openInPopup(800, 600);
    $slide = WebSearchAction::make()->query(fn (): string => 'x')->engine(Swisscows::class)->openInSlideOver();
    $refused = WebSearchAction::make()->query(fn (): string => 'x')->engine(Google::class)->openInSlideOver();

    expect($same->shouldOpenUrlInNewTab())->toBeFalse()
        ->and($same->getExtraAttributes())->toBe([])
        ->and($popup->getExtraAttributes()['onclick'])->toContain('width=800,height=600')
        ->and($slide->getUrl())->toBeNull()
        ->and($slide->shouldOpenModal())->toBeTrue()
        ->and($slide->isModalSlideOver())->toBeTrue()
        ->and($slide->getModalContent()->render())->toContain('swisscows.com/en/web?query=x')
        ->and($refused->getUrl())->toBe('https://www.google.com/search?q=x')
        ->and($refused->shouldOpenModal())->toBeFalse();
});

it('takes the engine, open mode and country from the plugin', function () {
    $plugin = WebSearchPlugin::make()->defaultEngine(DuckDuckGo::class)->openIn(OpenIn::SameTab);
    bootPanel()->plugin($plugin);

    $action = WebSearchAction::make()->query(fn (): string => 'x')->country(Country::IT);

    expect($action->getUrl())->toBe('https://duckduckgo.com/?q=x&kl=it-en')
        ->and($action->shouldOpenUrlInNewTab())->toBeFalse();
});

it('honours the configured permission', function () {
    config()->set('websearch-for-filament.permission', 'use_websearch');
    bootPanel();

    $action = WebSearchAction::make()->query(fn (): string => 'x');

    expect($action->isVisible())->toBeFalse();

    Gate::define('use_websearch', fn (): bool => true);
    loginUser();

    expect($action->isVisible())->toBeTrue();
});

it('routes clicks through a signed redirect when tracking is on', function () {
    Event::fake([SearchOpened::class]);
    bootPanel();
    $user = loginUser();

    $href = WebSearchAction::make()->query(fn (): string => 'x')->engine(Bing::class)->images()->track()->getHref();

    expect($href)->toContain('/admin/websearch/open?')->toContain('signature=');

    $this->get($href)->assertRedirect('https://www.bing.com/images/search?q=x');

    Event::assertDispatched(SearchOpened::class, fn (SearchOpened $event): bool => $event->user?->is($user)
        && $event->engine === Bing::class
        && $event->query === 'x'
        && $event->url === 'https://www.bing.com/images/search?q=x');

    $this->get(str_replace('signature=', 'signature=0', $href))->assertForbidden();
});

it('fires the event when a slide-over mounts with tracking on', function () {
    Event::fake([SearchOpened::class]);
    bootPanel();
    loginUser();

    $action = WebSearchAction::make()->query(fn (): string => 'x')->engine(Swisscows::class)->openInSlideOver()->track();
    $action->evaluate($action->getMountUsing());

    Event::assertDispatched(SearchOpened::class);
});
