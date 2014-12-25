<?php

namespace Brera\PoC\Product;

use Brera\PoC\SnippetRendererCollection;
use Brera\PoC\ProjectionSourceData;
use Brera\PoC\Environment;
use Brera\PoC\SnippetResultList;
use Brera\PoC\InvalidProjectionDataSourceType;
use Brera\PoC\SnippetRenderer;

abstract class ProductSnippetRendererCollection implements SnippetRendererCollection
{
    /**
     * @param ProjectionSourceData $product
     * @param Environment $environment
     * @return SnippetResultList
     * @throws InvalidProjectionDataSourceType
     */
    final public function render(ProjectionSourceData $product, Environment $environment)
    {
        if (!($product instanceof Product)) {
            throw new InvalidProjectionDataSourceType('First argument must be instance of Product.');
        }
        return $this->renderProduct($product, $environment);
    }

    /**
     * @return SnippetResultList
     */
    abstract protected function getSnippetResultList();

    /**
     * @return SnippetRenderer[]
     */
    abstract protected function getSnippetRenderers();

    /**
     * @param Product $product
     * @param Environment $environment
     * @return SnippetResultList
     */
    private function renderProduct(Product $product, Environment $environment)
    {
        $snippetResultList = $this->getSnippetResultList();
        if ($rendererList = $this->getSnippetRenderers()) {
            foreach ($rendererList as $renderer) {
                $snippetResultList->merge($renderer->render($product, $environment));
            }
        }
        return $snippetResultList;
    }
}
