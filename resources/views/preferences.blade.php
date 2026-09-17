<x-filament::section
    :aside="true"
    :heading="__('websearch-for-filament::websearch.preferences.heading')"
    :description="__('websearch-for-filament::websearch.preferences.description')"
>
    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}

        <div class="text-right">
            <x-filament::button type="submit" form="submit">
                {{ __('websearch-for-filament::websearch.preferences.submit') }}
            </x-filament::button>
        </div>
    </form>
</x-filament::section>
