<?php

namespace Brera\Product;

use Brera\Environment\EnvironmentSource;
use Brera\SnippetRendererCollection;
use Brera\ProjectionSourceData;
use Brera\SnippetResultList;
use Brera\InvalidProjectionDataSourceType;
use Brera\SnippetRenderer;

class ProductSnippetRendererCollection implements SnippetRendererCollection
{
    /**
     * @var SnippetResultList
     */
    private $snippetResultList;

    /**
     * @var SnippetRenderer[]
     */
    private $renderers = [];

    /**
     * @param SnippetRenderer[] $renderers
     * @param SnippetResultList $snippetResultList
     */
    public function __construct(array $renderers, SnippetResultList $snippetResultList)
    {
        $this->renderers = $renderers;
        $this->snippetResultList = $snippetResultList;
    }
    
    /**
     * @param ProjectionSourceData $productSource
     * @param EnvironmentSource $environmentSource
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $productSource, EnvironmentSource $environmentSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidProjectionDataSourceType('First argument must be instance of Product.');
        }
        
        $this->renderProduct($productSource, $environmentSource);
        return $this->snippetResultList;
    }

    /**
     * @param ProductSource $productSource
     * @param EnvironmentSource $environmentSource
     * @return void
     */
    private function renderProduct(ProductSource $productSource, EnvironmentSource $environmentSource)
    {
        foreach ($this->renderers as $renderer) {
            $this->snippetResultList->merge($renderer->render($productSource, $environmentSource));
        }
    }
}
