<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

class DefaultNumberOfProductsPerPageSnippetRenderer implements SnippetRenderer
{
    const CODE = 'default_number_of_products_per_page';

    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;
    
    /**
     * @var ContextSource
     */
    private $contextSource;

    public function __construct(
        SnippetList $snippetList,
        SnippetKeyGenerator $snippetKeyGenerator,
        ContextSource $contextSource
    ) {
        $this->snippetList = $snippetList;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->contextSource = $contextSource;
    }

    /**
     * @param ProductsPerPageForContextList $productsPerPageForContextList
     * @return SnippetList
     */
    public function render(ProductsPerPageForContextList $productsPerPageForContextList)
    {
        $contextParts = $this->snippetKeyGenerator->getContextPartsUsedForKey();
        foreach ($this->contextSource->getContextsForParts($contextParts) as $context) {
            $this->renderSnippetInContext($productsPerPageForContextList, $context);
        }

        return $this->snippetList;
    }

    private function renderSnippetInContext(ProductsPerPageForContextList $productsPerPageList, Context $context)
    {
        $snippetKey = $this->snippetKeyGenerator->getKeyForContext($context, []);
        $productsPerPage = $productsPerPageList->getListOfAvailableNumberOfProductsPerPageForContext($context);
        $snippetContent = array_shift($productsPerPage);
        $snippet = Snippet::create($snippetKey, $snippetContent);
        $this->snippetList->add($snippet);
    }
}
