<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
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
     * @var ProductInContextDetailViewSnippetRenderer
     */
    private $productInContextRenderer;

    public function __construct(
        SnippetResultList $snippetResultList,
        ProductInContextDetailViewSnippetRenderer $productInContextRenderer
    ) {
        $this->snippetResultList = $snippetResultList;
        $this->productInContextRenderer = $productInContextRenderer;
    }

    /**
     * @param ProjectionSourceData|ProductSource $productSource
     * @param ContextSource $contextSource
     * @throws InvalidArgumentException
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidArgumentException('First argument must be instance of Product.');
        }
        $this->productSource = $productSource;
        $this->contextSource = $contextSource;
        $this->createProductDetailViewSnippets();

        return $this->snippetResultList;
    }

    private function createProductDetailViewSnippets()
    {
        foreach ($this->contextSource->extractContexts($this->getContextParts()) as $context) {
            $productInContext = $this->productSource->getProductForContext($context);
            $inContextSnippetResultList = $this->productInContextRenderer->render($productInContext, $context);
            $this->snippetResultList->merge($inContextSnippetResultList);
        }
    }

    /**
     * @return string[]
     */
    private function getContextParts()
    {
        return ['version', 'website', 'language'];
    }
}
