<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

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
     * @param SimpleProduct $product
     * @return SnippetList
     */
    public function render(SimpleProduct $product)
    {
        $this->renderBackOrderAvailabilitySnippet($product);

        return $this->snippetList;
    }

    private function renderBackOrderAvailabilitySnippet(SimpleProduct $product)
    {
        $context = $product->getContext();
        $snippetKey = $this->snippetKeyGenerator->getKeyForContext($context, [SimpleProduct::ID => $product->getId()]);
        $snippetContent = $product->getFirstValueOfAttribute($this->backOrderAvailabilityAttributeCode);
        $snippet = Snippet::create($snippetKey, $snippetContent);

        $this->snippetList->add($snippet);
    }
}
