<?php

namespace Blemli\WebSearch\Http\Controllers;

use Blemli\WebSearch\Enums\SearchType;
use Blemli\WebSearch\Events\SearchOpened;
use Blemli\WebSearch\WebSearch;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Signed redirect that announces the search before handing over to the
 * engine, so links stay plain anchors (middle-click, cmd-click, copy).
 * The signature covers the target URL, so it cannot be repointed.
 */
class OpenSearchController
{
    public function __invoke(Request $request, WebSearch $webSearch): RedirectResponse
    {
        $engine = $webSearch->resolveEngine((string) $request->query('engine'));
        $type = SearchType::from((string) $request->query('type'));
        $query = (string) $request->query('q');
        $url = (string) $request->query('url');

        SearchOpened::dispatch(Filament::auth()->user(), $engine, $type, $query, $url);

        return redirect()->away($url);
    }
}
