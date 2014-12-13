<?php

namespace Brera\PoC;

use Brera\PoC\Product\Product;

class HardcodedProductSnippetRendererCollection
{

    /**
     * @var SnippetResultList
     */
    private $snippetResultList;
    /**
     * @var ProductSnippetRenderer[]
     */
    private $renderer;

    public function __construct(
        array $renderer,
        SnippetResultList $snippetResultList
    ) {
        $this->snippetResultList = $snippetResultList;
        $this->renderer = $renderer;
    }

    public function render(Product $product, Environment $environment)
    {
        foreach ($this->renderer as $renderer) {
            $this->snippetResultList
                ->merge($renderer->render($product, $environment));
        }

        return $this->snippetResultList;
    }
}
