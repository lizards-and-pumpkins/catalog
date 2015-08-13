<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\SnippetRenderer;
use Brera\SnippetList;
use Brera\ProjectionSourceData;

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
     * @param ProjectionSourceData|ProductSource $productSource
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        $this->validateProjectionSourceData($productSource);
        $this->initProperties($productSource, $contextSource);
        
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

    /**
     * @param ProjectionSourceData $productSource
     * @throws InvalidProjectionDataSourceTypeException
     */
    private function validateProjectionSourceData(ProjectionSourceData $productSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidProjectionDataSourceTypeException('First argument must be instance of Product.');
        }
    }

    private function initProperties(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        $this->productSource = $productSource;
        $this->contextSource = $contextSource;
    }
}
