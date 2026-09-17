<?php

namespace Blemli\WebSearch\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\confirm;

class UninstallCommand extends Command
{
    public $signature = 'websearch:uninstall {--force : Skip all confirmation prompts}';

    public $description = 'Uninstall websearch-for-filament: drop its table and remove published files';

    public function handle(): int
    {
        $this->removeTable();
        $this->removePublishedFiles();

        $registrations = $this->findPluginRegistrations();

        if ($this->option('force')) {
            $this->warnAboutRegistrations($registrations);
        } else {
            while ($registrations !== []) {
                $this->warnAboutRegistrations($registrations);

                if (! confirm('Removed it?', default: false)) {
                    break;
                }

                $registrations = $this->findPluginRegistrations();
            }

            if ($registrations === []) {
                $this->info('No WebSearchPlugin registration left.');
            }
        }

        if (! $this->option('force') && confirm('Run "composer remove blemli/websearch-for-filament" now?', default: $registrations === [])) {
            Process::path(base_path())
                ->forever()
                ->run(['composer', 'remove', 'blemli/websearch-for-filament'], fn (string $type, string $output) => $this->output->write($output));

            $this->info('websearch-for-filament was uninstalled.');
        } else {
            $this->info('websearch-for-filament was uninstalled. Finish with: composer remove blemli/websearch-for-filament');
        }

        return self::SUCCESS;
    }

    protected function removeTable(): void
    {
        if (! Schema::hasTable('websearch_preferences')) {
            return;
        }

        $this->info('The websearch_preferences table (user search preferences) will be dropped.');

        if ($this->option('force') || confirm('Drop the websearch_preferences table?')) {
            Schema::drop('websearch_preferences');
            $this->info('Dropped websearch_preferences.');
        }
    }

    protected function removePublishedFiles(): void
    {
        $paths = array_filter([
            config_path('websearch-for-filament.php'),
            lang_path('vendor/websearch-for-filament'),
            resource_path('views/vendor/websearch-for-filament'),
            ...File::glob(database_path('migrations/*_create_websearch_preferences_table.php')),
        ], fn (string $path): bool => File::exists($path));

        if ($paths === []) {
            $this->info('Nothing published to remove.');

            return;
        }

        $this->info('The following will be removed:');

        foreach ($paths as $path) {
            $this->line("  - {$path}");
        }

        if (! $this->option('force') && ! confirm('Delete published config, translations, views and migrations?')) {
            return;
        }

        foreach ($paths as $path) {
            File::isDirectory($path) ? File::deleteDirectory($path) : File::delete($path);
        }
    }

    /**
     * @param  array<string>  $registrations
     */
    protected function warnAboutRegistrations(array $registrations): void
    {
        if ($registrations === []) {
            return;
        }

        $this->warn('WebSearchPlugin is still registered in your panel provider(s) — remove the ->plugin(WebSearchPlugin::make()...) call or the app will crash after composer remove:');

        foreach ($registrations as $location) {
            $this->line("  - {$location}");
        }
    }

    /**
     * Find WebSearchPlugin registrations in the app's providers so the user
     * can remove them before the package code disappears. The file is never
     * edited automatically — that is the user's code.
     *
     * @return array<string>
     */
    protected function findPluginRegistrations(): array
    {
        $locations = [];

        if (! File::isDirectory(app_path('Providers'))) {
            return $locations;
        }

        foreach (File::allFiles(app_path('Providers')) as $file) {
            foreach (explode("\n", File::get($file->getPathname())) as $index => $line) {
                if (str_contains($line, 'WebSearchPlugin')) {
                    $locations[] = $file->getPathname() . ':' . ($index + 1);
                }
            }
        }

        return $locations;
    }
}
