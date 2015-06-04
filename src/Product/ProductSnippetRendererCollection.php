<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\SnippetRendererCollection;
use Brera\ProjectionSourceData;
use Brera\SnippetList;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\SnippetRenderer;

class ProductSnippetRendererCollection implements SnippetRendererCollection
{
    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var SnippetRenderer[]
     */
    private $renderers = [];

    /**
     * @param SnippetRenderer[] $renderers
     * @param SnippetList $snippetList
     */
    public function __construct(array $renderers, SnippetList $snippetList)
    {
        $this->renderers = $renderers;
        $this->snippetList = $snippetList;
    }
    
    /**
     * @param ProjectionSourceData $productSource
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidProjectionDataSourceTypeException('First argument must be instance of ProductSource.');
        }
        
        $this->renderSnippet($productSource, $contextSource);
        return $this->snippetList;
    }

    private function renderSnippet(ProductSource $productSource, ContextSource $contextSource)
    {
        foreach ($this->renderers as $renderer) {
            $this->snippetList->merge($renderer->render($productSource, $contextSource));
        }
    }
}
