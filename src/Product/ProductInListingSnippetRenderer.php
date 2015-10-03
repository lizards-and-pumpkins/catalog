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
     * @return SnippetList
     */
    public function render($projectionSourceData)
    {
        if (!($projectionSourceData instanceof SimpleProduct)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be a Product instance.');
        }

        $this->addProductInListingSnippetsToList($projectionSourceData);

        return $this->snippetList;
    }

    private function addProductInListingSnippetsToList(Product $product)
    {
        $this->addProductInListingInContextSnippetsToList($product);
    }

    private function addProductInListingInContextSnippetsToList(Product $product)
    {
        $key = $this->snippetKeyGenerator->getKeyForContext($product->getContext(), [SimpleProduct::ID => $product->getId()]);
        $content = json_encode($product);

        $contentSnippet = Snippet::create($key, $content);

        $this->snippetList->add($contentSnippet);
    }
}
