<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\SnippetRendererCollection;
use Brera\ProjectionSourceData;
use Brera\SnippetResultList;
use Brera\InvalidProjectionDataSourceTypeException;
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
     * @param ContextSource $contextSource
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidProjectionDataSourceTypeException('First argument must be instance of Product.');
        }
        
        $this->renderProduct($productSource, $contextSource);
        return $this->snippetResultList;
    }

    /**
     * @param ProductSource $productSource
     * @param ContextSource $contextSource
     * @return void
     */
    private function renderProduct(ProductSource $productSource, ContextSource $contextSource)
    {
        foreach ($this->renderers as $renderer) {
            $this->snippetResultList->merge($renderer->render($productSource, $contextSource));
        }
    }
}
