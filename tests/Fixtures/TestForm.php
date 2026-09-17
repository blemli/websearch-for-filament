<?php

namespace Blemli\WebSearch\Tests\Fixtures;

use Blemli\WebSearch\Actions\WebSearchAction;
use Blemli\WebSearch\Engines\DuckDuckGo;
use Blemli\WebSearch\Engines\Google;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TestForm extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->websearch(),
                TextInput::make('vendor'),
                TextInput::make('image')
                    ->imagesearch(['vendor', 'name'], fn (WebSearchAction $action) => $action->engine(DuckDuckGo::class)->transparent()),
                TextInput::make('manual')
                    ->hintAction(WebSearchAction::make()->engine(Google::class)->query(fn ($get): string => trim($get('vendor') . ' ' . $get('name') . ' manual'))),
            ])
            ->statePath('data');
    }

    public function render(): View
    {
        return view('websearch-for-filament::test-form');
    }
}
