# Changelog

All notable changes to `websearch-for-filament` will be documented in this file.

## v0.1.1 - 2026-09-17

- Uninstaller: `--drop-tables` and `--remove-composer` flags, safe defaults under `--no-interaction`
- README: `filament-hidden` banner and slogan for the plugin directory
- Light-theme banner

## v0.1.0 - 2026-09-17

- `->websearch()` and `->imagesearch()` macros on form fields and infolist entries
- `WebSearchAction` for any action slot: query from the field, a list of fields or a closure
- Engines: Google, DuckDuckGo, Bing, Brave, Swisscows, Ecosia, Startpage, Qwant, Yandex, Baidu — each with fluent builders exposing only what it supports
- Harmonised filters: search type, license, color/transparency, size, layout, video duration, timespan/date range, country
- Open in new tab, same tab, popup or slide-over (engines that allow framing)
- User choice of engine with cookie (default), user-attribute or database store; Filament Breezy profile section
- Devs restrict the open modes users may pick (`->openModes()` / `->exceptOpenModes()`)
- `SearchOpened` event with signed tracking redirect and optional activitylog listener
- Gate permission for Filament Shield
- Install and uninstall commands, English and German translations
