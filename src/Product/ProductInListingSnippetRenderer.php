<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

class ProductInListingSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_in_listing';

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;
    
    public function __construct(SnippetKeyGenerator $snippetKeyGenerator)
    {
        $this->snippetKeyGenerator = $snippetKeyGenerator;
    }

    /**
     * @param mixed $projectionSourceData
     * @return Snippet[]
     */
    public function render($projectionSourceData)
    {
        if (!($projectionSourceData instanceof ProductView)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be a ProductView instance.');
        }

        return [
            $this->getProductInListingSnippet($projectionSourceData)
        ];
    }

    /**
     * @param ProductView $product
     * @return Snippet
     */
    private function getProductInListingSnippet(ProductView $product)
    {
        $key = $this->snippetKeyGenerator->getKeyForContext($product->getContext(), [Product::ID => $product->getId()]);
        $content = json_encode($product);
        return Snippet::create($key, $content);
    }
}
