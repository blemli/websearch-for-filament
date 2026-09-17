<?php

namespace Blemli\WebSearch\Engines;

use Blemli\WebSearch\Engines\Concerns\SearchesImages;
use Blemli\WebSearch\Engines\Concerns\SearchesNews;
use Blemli\WebSearch\Engines\Concerns\SearchesShopping;
use Blemli\WebSearch\Engines\Concerns\SearchesVideos;
use Blemli\WebSearch\Engines\Concerns\SearchesWeb;

class Qwant extends Engine
{
    use SearchesImages;
    use SearchesNews;
    use SearchesShopping;
    use SearchesVideos;
    use SearchesWeb;

    public function url(): string
    {
        return $this->buildUrl('https://www.qwant.com/', [
            'q' => $this->query,
            't' => $this->type->value,
        ]);
    }
}
