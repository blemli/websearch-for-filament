# websearch

Search the web from any Filament field.

![websearch](https://raw.githubusercontent.com/blemli/websearch-for-filament/main/art/banner.png)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/blemli/websearch-for-filament.svg?style=flat-square)](https://packagist.org/packages/blemli/websearch-for-filament) [![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/blemli/websearch-for-filament/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/blemli/websearch-for-filament/actions?query=workflow%3Atests+branch%3Amain) [![Total Downloads](https://img.shields.io/packagist/dt/blemli/websearch-for-filament.svg?style=flat-square)](https://packagist.org/packages/blemli/websearch-for-filament)

A hint action that opens a search for the field's value on Google, DuckDuckGo, Bing, Brave, Swisscows, Ecosia, Startpage, Qwant, Yandex or Baidu, with harmonised filters (images, videos, news, shopping, license, transparency, size, layout, timespan, country). Users can pick their own engine. No migration needed.

## Installation

```bash
composer require blemli/websearch-for-filament
php artisan websearch-for-filament:install
```

Register the plugin in your panel provider: `->plugin(WebSearchPlugin::make()->userChoice())`

## Usage

```php
TextInput::make('name')->websearch(),                                   // "Search on Google" for the field's value
FileUpload::make('image')->imagesearch(['vendor', 'name']),             // image search for two fields joined
FileUpload::make('image')->imagesearch('name', fn (WebSearchAction $a) => $a->transparent()->license(License::CreativeCommons)),

WebSearchAction::make()->query('name')->engine(DuckDuckGo::class)->news()->within(Timespan::Week), // any action slot

Google::images('Fujifilm GFX')->transparent()->layout(Layout::Tall)->url(); // just the URL, no Filament needed
```

Results open in a new tab by default; `->openInSameTab()`, `->openInPopup()` and `->openInSlideOver()` (Swisscows, Bing, Baidu) are available on the action and in the user's profile when [Filament Breezy](https://github.com/jeffgreco13/filament-breezy) is installed. `->trackSearches()` on the plugin fires a `SearchOpened` event and logs to [spatie/laravel-activitylog](https://github.com/spatie/laravel-activitylog) when present. Ships in English and German.

## License

MIT © [blemli](https://github.com/blemli)
