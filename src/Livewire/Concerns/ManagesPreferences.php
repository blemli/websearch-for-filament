<?php

namespace Blemli\WebSearch\Livewire\Concerns;

use Blemli\WebSearch\Engines\Engine;
use Blemli\WebSearch\Enums\Country;
use Blemli\WebSearch\Enums\OpenIn;
use Blemli\WebSearch\WebSearch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

/**
 * The preferences form, shared by the standalone component and the
 * Breezy profile section.
 */
trait ManagesPreferences
{
    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $manager = app(WebSearch::class);
        $preferences = $manager->preferencesFor();

        $this->getSchema('form')?->fill([
            'engine' => $preferences['engine'] ?? $manager->defaultEngine()::key(),
            'open_in' => $preferences['open_in'] ?? $manager->openInFor()->value,
            'country' => $preferences['country'] ?? null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $engines = app(WebSearch::class)->engines();
        $openModes = app(WebSearch::class)->openModes();

        return $schema
            ->components([
                ToggleButtons::make('engine')
                    ->label(__('websearch-for-filament::websearch.preferences.engine'))
                    ->options(collect($engines)->mapWithKeys(fn (string $engine): array => [$engine::key() => $engine::name()])->all())
                    ->icons(collect($engines)->mapWithKeys(fn (string $engine): array => [$engine::key() => $engine::icon()])->all())
                    ->inline()
                    ->required(),
                ToggleButtons::make('open_in')
                    ->label(__('websearch-for-filament::websearch.preferences.open_in'))
                    ->options(collect($openModes)->mapWithKeys(fn (OpenIn $mode): array => [$mode->value => $mode->getLabel()])->all())
                    ->inline()
                    ->required()
                    ->visible(count($openModes) > 1),
                Select::make('country')
                    ->label(__('websearch-for-filament::websearch.preferences.country'))
                    ->helperText(__('websearch-for-filament::websearch.preferences.country_help'))
                    ->options(collect(Country::cases())->mapWithKeys(fn (Country $country): array => [$country->value => $country->getLabel()])->sort()->all())
                    ->searchable()
                    ->native(false)
                    ->placeholder(__('websearch-for-filament::websearch.preferences.worldwide')),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        /** @var array{engine?: string | null, open_in?: string | null, country?: string | null} $state */
        $state = $this->getSchema('form')?->getState() ?? [];

        app(WebSearch::class)->savePreferences($state);

        Notification::make()
            ->success()
            ->title(__('websearch-for-filament::websearch.preferences.saved'))
            ->send();
    }

    /**
     * @return list<class-string<Engine>>
     */
    public function getEngines(): array
    {
        return app(WebSearch::class)->engines();
    }
}
