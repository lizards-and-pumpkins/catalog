<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\SnippetRenderer;
use Brera\SnippetResultList;
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
     * @var SnippetResultList
     */
    private $snippetResultList;

    /**
     * @var ProductDetailViewInContextSnippetRenderer
     */
    private $productInContextRenderer;

    public function __construct(
        SnippetResultList $snippetResultList,
        ProductDetailViewInContextSnippetRenderer $productInContextRenderer
    ) {
        $this->snippetResultList = $snippetResultList;
        $this->productInContextRenderer = $productInContextRenderer;
    }

    /**
     * @param ProjectionSourceData|ProductSource $productSource
     * @param ContextSource $contextSource
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        $this->validateProjectionSourceData($productSource);
        $this->initProperties($productSource, $contextSource);
        
        $this->createProductDetailViewSnippets();

        return $this->snippetResultList;
    }

    private function createProductDetailViewSnippets()
    {
        foreach ($this->getContextList() as $context) {
            $productInContext = $this->productSource->getProductForContext($context);
            $inContextSnippetResultList = $this->productInContextRenderer->render($productInContext, $context);
            $this->snippetResultList->merge($inContextSnippetResultList);
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
        $this->snippetResultList->clear();
    }
}
