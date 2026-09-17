{{-- Inline sizes: arbitrary Tailwind values are not part of the host's compiled CSS. --}}
<iframe
    src="{{ $url }}"
    title="{{ __('websearch-for-filament::websearch.embed.title') }}"
    style="display: block; width: 100%; height: calc(100vh - 7rem); border: 0; border-radius: 0.5rem; background: #fff;"
    referrerpolicy="no-referrer"
></iframe>
