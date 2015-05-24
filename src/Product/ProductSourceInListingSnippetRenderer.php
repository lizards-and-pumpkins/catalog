<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\ProjectionSourceData;
use Brera\SnippetRenderer;
use Brera\SnippetList;

class ProductSourceInListingSnippetRenderer implements SnippetRenderer
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
     * @var ProductInListingInContextSnippetRenderer
     */
    private $productInContextRenderer;

    public function __construct(
        SnippetList $snippetList,
        ProductInListingInContextSnippetRenderer $productInContextRenderer
    ) {
        $this->snippetList = $snippetList;
        $this->productInContextRenderer = $productInContextRenderer;
    }

    /**
     * @param ProjectionSourceData $productSource
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        $this->validateProjectionSourceData($productSource);
        $this->initProperties($productSource, $contextSource);

        $this->createProductInListingSnippets();

        return $this->snippetList;
    }

    private function createProductInListingSnippets()
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
        return $this->contextSource->getAllAvailableContexts($parts);
    }

    /**
     * @param ProjectionSourceData $productSource
     * @throws InvalidProjectionDataSourceTypeException
     */
    private function validateProjectionSourceData(ProjectionSourceData $productSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidProjectionDataSourceTypeException('First argument must be instance of ProductSource.');
        }
    }

    private function initProperties(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        $this->productSource = $productSource;
        $this->contextSource = $contextSource;
        $this->snippetList->clear();
    }
}
