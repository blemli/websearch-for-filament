<?php

use Blemli\WebSearch\Engines\Baidu;
use Blemli\WebSearch\Engines\Bing;
use Blemli\WebSearch\Engines\Brave;
use Blemli\WebSearch\Engines\DuckDuckGo;
use Blemli\WebSearch\Engines\Ecosia;
use Blemli\WebSearch\Engines\Google;
use Blemli\WebSearch\Engines\Qwant;
use Blemli\WebSearch\Engines\Startpage;
use Blemli\WebSearch\Engines\Swisscows;
use Blemli\WebSearch\Engines\Yandex;

return [

    /*
     * The engine used when neither the action nor the user picked one.
     */
    'default' => Google::class,

    /*
     * Engines users may choose from. Any Blemli\WebSearch\Engines\Engine
     * subclass works here, including your own.
     */
    'engines' => [
        Google::class,
        DuckDuckGo::class,
        Bing::class,
        Brave::class,
        Swisscows::class,
        Ecosia::class,
        Startpage::class,
        Qwant::class,
        Yandex::class,
        Baidu::class,
    ],

    /*
     * How results open: new_tab, same_tab, popup or slide_over. Engines that
     * refuse to be framed fall back from slide_over to a new tab.
     */
    'open_in' => 'new_tab',

    /*
     * Let users pick their engine (and how results open) in their profile.
     */
    'user_choice' => false,

    /*
     * Where that choice lives: 'cookie' (per browser, no migration),
     * 'attribute' (a JSON column on the user model, name below) or
     * 'database' (publish and run the package migration). A class
     * implementing Blemli\WebSearch\Stores\PreferenceStore works too.
     */
    'store' => 'cookie',

    'attribute' => 'websearch_preferences',

    /*
     * Route every click through a signed redirect that fires the
     * SearchOpened event (and, with spatie/laravel-activitylog installed,
     * writes an activity log entry) before sending the user on.
     */
    'track' => false,

    /*
     * Gate ability required to see search actions, e.g. 'use_websearch'
     * (add it to Filament Shield's custom permissions). Null shows them
     * to everyone.
     */
    'permission' => null,

];
