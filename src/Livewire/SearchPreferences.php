<?php

namespace Blemli\WebSearch\Livewire;

use Blemli\WebSearch\Livewire\Concerns\ManagesPreferences;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Drop-in preferences section: @livewire('websearch-preferences').
 */
class SearchPreferences extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms, ManagesPreferences {
        ManagesPreferences::form insteadof InteractsWithForms;
    }

    public function render(): View
    {
        return view('websearch-for-filament::preferences');
    }
}
