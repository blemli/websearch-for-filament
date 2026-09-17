<?php

use Blemli\WebSearch\Commands\UninstallCommand;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

function fakePublishedPaths(): array
{
    return [
        config_path('websearch-for-filament.php'),
        lang_path('vendor/websearch-for-filament/de/websearch.php'),
        resource_path('views/vendor/websearch-for-filament/embed.blade.php'),
        database_path('migrations/2026_01_01_000000_create_websearch_preferences_table.php'),
    ];
}

function publishFakeArtifacts(): array
{
    foreach (fakePublishedPaths() as $path) {
        File::ensureDirectoryExists(dirname($path));
        File::put($path, str_contains($path, 'migrations')
            ? "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\n\nreturn new class extends Migration\n{\n    public function up(): void {}\n};\n"
            : '<?php return [];');
    }

    return fakePublishedPaths();
}

afterEach(function () {
    foreach (fakePublishedPaths() as $path) {
        File::delete($path);
    }

    File::deleteDirectory(lang_path('vendor/websearch-for-filament'));
    File::deleteDirectory(resource_path('views/vendor/websearch-for-filament'));
    File::deleteDirectory(app_path('Providers/Filament'));
});

it('removes every published artifact and drops the table on uninstall', function () {
    $paths = publishFakeArtifacts();
    createPreferencesTable();

    $this->artisan('websearch:uninstall', ['--force' => true])->assertSuccessful();

    foreach ($paths as $path) {
        expect(File::exists($path))->toBeFalse($path . ' should have been removed');
    }

    expect(Schema::hasTable('websearch_preferences'))->toBeFalse();
});

it('points to panel providers that still register the plugin', function () {
    $provider = app_path('Providers/Filament/AdminPanelProvider.php');
    File::ensureDirectoryExists(dirname($provider));
    File::put($provider, "<?php\n\n// ...\n\$panel->plugin(WebSearchPlugin::make());\n");

    $this->artisan('websearch:uninstall', ['--force' => true])
        ->expectsOutputToContain('WebSearchPlugin is still registered')
        ->expectsOutputToContain('AdminPanelProvider.php:4')
        ->assertSuccessful();
});

it('loops until the registration is gone and never edits the provider', function () {
    $provider = app_path('Providers/Filament/AdminPanelProvider.php');
    File::ensureDirectoryExists(dirname($provider));
    File::put($provider, "<?php\n\n// ...\n\$panel->plugin(WebSearchPlugin::make());\n");
    $before = File::get($provider);

    $this->artisan('websearch:uninstall')
        ->expectsConfirmation('Removed it?', 'yes')
        ->expectsConfirmation('Removed it?', 'no')
        ->expectsConfirmation('Run "composer remove blemli/websearch-for-filament" now?', 'no')
        ->assertSuccessful();

    expect(File::get($provider))->toBe($before);
});

it('exits the loop once the registration disappears', function () {
    $command = new class extends UninstallCommand
    {
        public array $scans = [];

        protected function findPluginRegistrations(): array
        {
            return array_shift($this->scans) ?? [];
        }
    };
    $command->scans = [[app_path('Providers/Filament/AdminPanelProvider.php') . ':4'], []];

    app(Kernel::class)->registerCommand($command);

    $this->artisan('websearch:uninstall')
        ->expectsConfirmation('Removed it?', 'yes')
        ->expectsOutputToContain('No WebSearchPlugin registration left')
        ->expectsConfirmation('Run "composer remove blemli/websearch-for-filament" now?', 'no')
        ->assertSuccessful();
});

it('never asks under --force and never runs composer', function () {
    $provider = app_path('Providers/Filament/AdminPanelProvider.php');
    File::ensureDirectoryExists(dirname($provider));
    File::put($provider, "<?php\n\n\$panel->plugin(WebSearchPlugin::make());\n");

    $this->artisan('websearch:uninstall', ['--force' => true])
        ->expectsOutputToContain('Finish with: composer remove blemli/websearch-for-filament')
        ->assertSuccessful();
});

it('prints english output regardless of the app locale', function () {
    app()->setLocale('de');
    publishFakeArtifacts();

    $this->artisan('websearch:uninstall', ['--force' => true])
        ->expectsOutputToContain('The following will be removed:')
        ->expectsOutputToContain('websearch-for-filament was uninstalled. Finish with: composer remove blemli/websearch-for-filament')
        ->assertSuccessful();
});
