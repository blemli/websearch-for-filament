<?php

namespace Blemli\WebSearch\Engines\Concerns;

use Blemli\WebSearch\Enums\Layout;

trait FiltersByLayout
{
    /**
     * @var list<Layout>
     */
    protected array $layouts = [];

    public function layout(?Layout ...$layouts): static
    {
        $this->layouts = array_values(array_filter($layouts));

        return $this;
    }

    /**
     * @return list<Layout>
     */
    public function getLayouts(): array
    {
        return $this->layouts;
    }

    /**
     * The first requested layout the engine can express.
     *
     * @param  array<string, string>  $map  Layout value => engine value
     */
    protected function firstSupportedLayout(array $map): ?string
    {
        foreach ($this->layouts as $layout) {
            if (isset($map[$layout->value])) {
                return $map[$layout->value];
            }
        }

        return null;
    }
}
