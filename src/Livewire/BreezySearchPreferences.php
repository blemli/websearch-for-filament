<?php

namespace Blemli\WebSearch\Livewire;

use Blemli\WebSearch\Livewire\Concerns\ManagesPreferences;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;

/**
 * The same section as a Filament Breezy "My Profile" component.
 */
class BreezySearchPreferences extends MyProfileComponent
{
    use ManagesPreferences;

    protected string $view = 'websearch-for-filament::preferences';

    public static $sort = 40;

    /**
     * Breezy derives the Livewire name from the class name; Livewire then
     * has to resolve that name back to this class, so it must match the
     * alias the service provider registers.
     */
    public function getName(): string
    {
        return 'websearch-breezy-preferences';
    }
}
