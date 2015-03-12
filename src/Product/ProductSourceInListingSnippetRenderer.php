<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\ProjectionSourceData;
use Brera\SnippetRenderer;
use Brera\SnippetResultList;

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
     * @var SnippetResultList
     */
    private $snippetResultList;

    /**
     * @var ProductInContextInListingSnippetRenderer
     */
    private $productInContextRenderer;

    /**
     * @param SnippetResultList $snippetResultList
     * @param ProductInContextInListingSnippetRenderer $productInContextRenderer
     */
    public function __construct(
        SnippetResultList $snippetResultList,
        ProductInContextInListingSnippetRenderer $productInContextRenderer
    ) {
        $this->snippetResultList = $snippetResultList;
        $this->productInContextRenderer = $productInContextRenderer;
    }

    /**
     * @param ProjectionSourceData $productSource
     * @param ContextSource $contextSource
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        $this->validateProjectionSourceData($productSource);
        $this->initProperties($productSource, $contextSource);

        $this->createProductInListingSnippets();

        return $this->snippetResultList;
    }

    private function createProductInListingSnippets()
    {
        foreach ($this->contextSource->getAllAvailableContexts() as $context) {
            $productInContext = $this->productSource->getProductForContext($context);
            $inContextSnippetResultList = $this->productInContextRenderer->render($productInContext, $context);
            $this->snippetResultList->merge($inContextSnippetResultList);
        }
    }

    /**
     * @param ProjectionSourceData $productSource
     * @throws InvalidArgumentException
     */
    private function validateProjectionSourceData(ProjectionSourceData $productSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidArgumentException('First argument must be instance of Product.');
        }
    }

    /**
     * @param ProjectionSourceData $productSource
     * @param ContextSource $contextSource
     */
    private function initProperties(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        $this->productSource = $productSource;
        $this->contextSource = $contextSource;
        $this->snippetResultList->clear();
    }
}
