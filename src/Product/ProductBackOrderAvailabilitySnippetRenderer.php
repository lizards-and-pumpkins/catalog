<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

class ProductBackOrderAvailabilitySnippetRenderer implements SnippetRenderer
{
    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var string
     */
    private $backOrderAvailabilityAttributeCode;

    /**
     * @param SnippetList $snippetList
     * @param SnippetKeyGenerator $snippetKeyGenerator
     * @param string $backOrderAvailabilityAttributeCode
     */
    public function __construct(
        SnippetList $snippetList,
        SnippetKeyGenerator $snippetKeyGenerator,
        $backOrderAvailabilityAttributeCode
    ) {
        $this->snippetList = $snippetList;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->backOrderAvailabilityAttributeCode = $backOrderAvailabilityAttributeCode;
    }

    /**
     * @param ProductSource $productSource
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render(ProductSource $productSource, ContextSource $contextSource)
    {
        $availableContexts = $contextSource->getAllAvailableContexts();
        foreach ($availableContexts as $context) {
            $this->renderBackOrderAvailabilitySnippetInContext($productSource, $context);
        }

        return $this->snippetList;
    }

    private function renderBackOrderAvailabilitySnippetInContext(ProductSource $productSource, Context $context)
    {
        $productInContext = $productSource->getProductForContext($context);

        $snippetKey = $this->snippetKeyGenerator->getKeyForContext($context, ['product_id' => $productSource->getId()]);
        $snippetContent = $productInContext->getFirstValueOfAttribute($this->backOrderAvailabilityAttributeCode);
        $snippet = Snippet::create($snippetKey, $snippetContent);

        $this->snippetList->add($snippet);
    }
}
