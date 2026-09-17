<?php

namespace Blemli\WebSearch;

use BladeUI\Icons\Factory as IconFactory;
use Blemli\WebSearch\Actions\WebSearchAction;
use Blemli\WebSearch\Commands\UninstallCommand;
use Blemli\WebSearch\Events\SearchOpened;
use Blemli\WebSearch\Listeners\LogSearchToActivitylog;
use Blemli\WebSearch\Livewire\BreezySearchPreferences;
use Blemli\WebSearch\Livewire\SearchPreferences;
use Closure;
use Filament\Forms\Components\Field;
use Filament\Infolists\Components\Entry;
use Illuminate\Support\Facades\Event;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WebSearchServiceProvider extends PackageServiceProvider
{
    public static string $name = 'websearch-for-filament';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigration('create_websearch_preferences_table')
            ->hasCommands([UninstallCommand::class])
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->publishConfigFile()
                    ->endWith(function (InstallCommand $command): void {
                        $command->info('Register the plugin: ->plugin(WebSearchPlugin::make()) in your panel provider.');
                        $command->info('Using the database preference store? Publish its migration: php artisan vendor:publish --tag=websearch-for-filament-migrations');
                    });
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(WebSearch::class);

        $this->callAfterResolving(IconFactory::class, function (IconFactory $factory): void {
            $factory->add('websearch', [
                'path' => __DIR__ . '/../resources/svg',
                'prefix' => 'websearch',
            ]);
        });
    }

    public function packageBooted(): void
    {
        Event::listen(SearchOpened::class, LogSearchToActivitylog::class);

        Livewire::component('websearch-preferences', SearchPreferences::class);

        if (class_exists(MyProfileComponent::class)) {
            Livewire::component('websearch-breezy-preferences', BreezySearchPreferences::class);
        }

        $this->registerMacros();
    }

    /**
     * ->websearch() / ->imagesearch() on form fields and infolist entries:
     * a hint action that searches the field's value (or the given fields).
     */
    protected function registerMacros(): void
    {
        foreach ([Field::class, Entry::class] as $component) {
            $component::macro('websearch', function (string | array | Closure | null $query = null, ?Closure $configure = null): Field | Entry {
                /** @var Field | Entry $this */
                $action = WebSearchAction::make()->query($query);

                if ($configure) {
                    $configure($action);
                }

                return $this->hintAction($action);
            });

            $component::macro('imagesearch', function (string | array | Closure | null $query = null, ?Closure $configure = null): Field | Entry {
                /** @var Field | Entry $this */
                $action = WebSearchAction::make('imagesearch')->images()->query($query);

                if ($configure) {
                    $configure($action);
                }

                return $this->hintAction($action);
            });
        }
    }
}
