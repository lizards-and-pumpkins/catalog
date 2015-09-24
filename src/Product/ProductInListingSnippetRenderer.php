<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\SnippetList;

class ProductInListingSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_in_listing';

    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    public function __construct(SnippetList $snippetList, SnippetKeyGenerator $snippetKeyGenerator)
    {
        $this->snippetList = $snippetList;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
    }

    /**
     * @param mixed $projectionSourceData
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render($projectionSourceData, ContextSource $contextSource)
    {
        if (!($projectionSourceData instanceof ProductSource)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be instance of ProductSource.');
        }

        $this->addProductInListingSnippetsToList($projectionSourceData, $contextSource);

        return $this->snippetList;
    }

    private function addProductInListingSnippetsToList(ProductSource $productSource, ContextSource $contextSource)
    {
        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $productInContext = $productSource->getProductForContext($context);
            $this->addProductInListingInContextSnippetsToList($productInContext, $context);
        }
    }

    private function addProductInListingInContextSnippetsToList(Product $product, Context $context)
    {
        $key = $this->snippetKeyGenerator->getKeyForContext($context, [Product::ID => $product->getId()]);
        $content = json_encode($product);

        $contentSnippet = Snippet::create($key, $content);

        $this->snippetList->add($contentSnippet);
    }
}
