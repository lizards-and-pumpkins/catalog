<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\InvalidProjectionSourceDataTypeException;
use Brera\SnippetRenderer;
use Brera\SnippetList;

class ProductSourceDetailViewSnippetRenderer implements SnippetRenderer
{
    /**
     * @var ProductSource
     */
    private $productSource;

    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var ProductDetailViewInContextSnippetRenderer
     */
    private $productInContextRenderer;

    public function __construct(
        SnippetList $snippetList,
        ProductDetailViewInContextSnippetRenderer $productInContextRenderer
    ) {
        $this->snippetList = $snippetList;
        $this->productInContextRenderer = $productInContextRenderer;
    }

    /**
     * @param mixed $projectionSourceData
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render($projectionSourceData, ContextSource $contextSource)
    {
        if (!($projectionSourceData instanceof ProductSource)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be instance of Product.');
        }

        $this->productSource = $projectionSourceData;
        $this->contextSource = $contextSource;
        $this->snippetList->clear();
        
        $this->createProductDetailViewSnippets();

        return $this->snippetList;
    }

    private function createProductDetailViewSnippets()
    {
        foreach ($this->getContextList() as $context) {
            $productInContext = $this->productSource->getProductForContext($context);
            $inContextSnippetList = $this->productInContextRenderer->render($productInContext, $context);
            $this->snippetList->merge($inContextSnippetList);
        }
    }

    /**
     * @return Context[]
     */
    private function getContextList()
    {
        $parts = $this->productInContextRenderer->getUsedContextParts();
        return $this->contextSource->getContextsForParts($parts);
    }
}
