<?php

use Blemli\WebSearch\Engines\Baidu;
use Blemli\WebSearch\Engines\Bing;
use Blemli\WebSearch\Engines\Brave;
use Blemli\WebSearch\Engines\DuckDuckGo;
use Blemli\WebSearch\Engines\Ecosia;
use Blemli\WebSearch\Engines\Engine;
use Blemli\WebSearch\Engines\Google;
use Blemli\WebSearch\Engines\Qwant;
use Blemli\WebSearch\Engines\Startpage;
use Blemli\WebSearch\Engines\Swisscows;
use Blemli\WebSearch\Engines\Yandex;
use Blemli\WebSearch\Enums\Color;
use Blemli\WebSearch\Enums\Country;
use Blemli\WebSearch\Enums\Duration;
use Blemli\WebSearch\Enums\ImageSize;
use Blemli\WebSearch\Enums\Layout;
use Blemli\WebSearch\Enums\License;
use Blemli\WebSearch\Enums\SearchType;
use Blemli\WebSearch\Enums\Timespan;
use Blemli\WebSearch\Support\SearchOptions;

function query(string $url): array
{
    parse_str((string) parse_url($url, PHP_URL_QUERY), $params);

    return $params;
}

it('builds a plain google web search', function () {
    $url = Google::web('Fujifilm GFX 100S')->url();

    expect($url)->toStartWith('https://www.google.com/search?')
        ->and(query($url))->toBe(['q' => 'Fujifilm GFX 100S']);
});

it('builds a google image search with transparent creative-commons images', function () {
    $params = query(Google::images('camera')->transparent()->license(License::CreativeCommons)->layout(Layout::Tall)->size(ImageSize::Large)->url());

    expect($params['tbm'])->toBe('isch')
        ->and($params['tbs'])->toBe('il:cl,ic:trans,isz:l,iar:t');
});

it('maps larger-than to google pixel buckets', function () {
    expect(query(Google::images('x')->largerThan(1024, 768)->url())['tbs'])->toBe('isz:lt,islt:xga')
        ->and(query(Google::images('x')->largerThan(3000, 2000)->url())['tbs'])->toBe('isz:lt,islt:6mp');
});

it('restricts google to a country and a date range', function () {
    $params = query(Google::web('x')->country(Country::CH)->between('2024-01-05', '2024-02-10')->url());

    expect($params)->toMatchArray(['cr' => 'countryCH', 'gl' => 'ch', 'tbs' => 'cdr:1,cd_min:1/5/2024,cd_max:2/10/2024']);
});

it('uses google time buckets and video duration', function () {
    expect(query(Google::news('x')->within(Timespan::Week)->url()))->toMatchArray(['tbm' => 'nws', 'tbs' => 'qdr:w'])
        ->and(query(Google::videos('x')->duration(Duration::Long)->url()))->toMatchArray(['tbm' => 'vid', 'tbs' => 'dur:l']);
});

it('derives the country from the app locale when country() has no argument', function () {
    app()->setLocale('de_CH');

    expect(query(Google::web('x')->country()->url())['gl'])->toBe('ch');

    app()->setLocale('de');
    config()->set('app.timezone', 'Europe/Berlin');

    expect(query(Google::web('x')->country()->url())['gl'])->toBe('de');
});

it('builds bing image, video and web filters', function () {
    $images = query(Bing::images('x')->transparent()->license(License::Commercial)->layout(Layout::Wide)->within(Timespan::Day)->country(Country::DE)->url());

    expect($images['qft'])->toBe('+filterui:age-lt1440+filterui:license-L2_L3_L4+filterui:photo-transparent+filterui:aspect-wide')
        ->and($images['cc'])->toBe('de');

    expect(query(Bing::videos('x')->duration(Duration::Short)->url())['qft'])->toBe('+filterui:videolength-short');
    expect(query(Bing::web('x')->within(Timespan::Week)->url())['filters'])->toBe('ex1:"ez2"');
    expect(query(Bing::web('x')->between('1970-01-11', '1970-01-21')->url())['filters'])->toBe('ex1:"ez5_10_20"');
});

it('builds duckduckgo searches', function () {
    app()->setLocale('de');

    $images = query(DuckDuckGo::images('camera')->transparent()->license(License::Modify)->size(ImageSize::Wallpaper)->layout(Layout::Square)->country(Country::CH)->url());

    expect($images)->toMatchArray([
        'q' => 'camera',
        'ia' => 'images',
        'iax' => 'images',
        'iaf' => 'size:Wallpaper,type:transparent,layout:Square,license:Modify',
        'kl' => 'ch-de',
    ]);

    expect(query(DuckDuckGo::web('x')->within(Timespan::Year)->url()))->toMatchArray(['df' => 'y'])
        ->and(query(DuckDuckGo::web('x')->between('2024-01-01', '2024-12-31')->url())['df'])->toBe('2024-01-01..2024-12-31')
        ->and(query(DuckDuckGo::news('x')->url()))->toMatchArray(['ia' => 'news', 'iar' => 'news'])
        ->and(query(DuckDuckGo::videos('x')->duration(Duration::Medium)->url())['iaf'])->toBe('videoDuration:medium')
        ->and(query(DuckDuckGo::web('x')->country(Country::GB)->url())['kl'])->toBe('uk-de');
});

it('builds brave, swisscows, startpage, ecosia, qwant, yandex and baidu urls', function () {
    expect(Brave::images('x')->url())->toBe('https://search.brave.com/images?q=x')
        ->and(query(Brave::web('x')->within(Timespan::Month)->country(Country::AT)->url()))->toMatchArray(['tf' => 'pm', 'country' => 'at'])
        ->and(query(Brave::web('x')->between('2024-01-01', '2024-02-01')->url())['tf'])->toBe('2024-01-01to2024-02-01');

    app()->setLocale('de_CH');
    expect(Swisscows::images('Kuh')->country()->within(Timespan::Week)->url())->toBe('https://swisscows.com/de/image?query=Kuh&region=de-CH&freshness=Week');

    expect(Startpage::videos('x')->within(Timespan::Day)->url())->toBe('https://www.startpage.com/do/search?q=x&cat=video&with_date=d')
        ->and(Ecosia::news('x')->url())->toBe('https://www.ecosia.org/news?q=x')
        ->and(Qwant::shopping('x')->url())->toBe('https://www.qwant.com/?q=x&t=shopping')
        ->and(Yandex::images('x')->size(ImageSize::Large)->color(Color::Purple)->layout(Layout::Tall)->url())->toBe('https://yandex.com/images/search?text=x&isize=large&icolor=violet&iorient=vertical')
        ->and(Baidu::images('相机')->url())->toBe('https://image.baidu.com/search/index?tn=baiduimage&word=%E7%9B%B8%E6%9C%BA');
});

it('encodes queries safely', function () {
    expect(Google::web('a&b=c d')->url())->toBe('https://www.google.com/search?q=a%26b%3Dc%20d');
});

it('knows what each engine supports', function () {
    expect(Google::supportedTypes())->toBe(SearchType::cases())
        ->and(Swisscows::supports(SearchType::Shopping))->toBeFalse()
        ->and(Swisscows::canBeEmbedded())->toBeTrue()
        ->and(Google::canBeEmbedded())->toBeFalse()
        ->and(DuckDuckGo::key())->toBe('duckduckgo')
        ->and(DuckDuckGo::name())->toBe('DuckDuckGo')
        ->and(Google::icon())->toBe('websearch-google')
        ->and(Bing::icon())->toBe('websearch-search');
});

it('refuses unsupported search types', function () {
    Swisscows::for(SearchType::Shopping, 'x');
})->throws(InvalidArgumentException::class);

it('applies harmonised options and drops what an engine cannot express', function () {
    $options = SearchOptions::make();
    $options->color = Color::Transparent;
    $options->license = License::CreativeCommons;
    $options->timespan = Timespan::Month;
    $options->country = Country::FR;

    expect(query(Google::images('x')->withOptions($options)->url()))->toMatchArray(['tbs' => 'qdr:m,il:cl,ic:trans', 'gl' => 'fr'])
        ->and(Ecosia::images('x')->withOptions($options)->url())->toBe('https://www.ecosia.org/images?q=x')
        ->and(Swisscows::images('x')->withOptions($options))->toBeInstanceOf(Engine::class);
});
