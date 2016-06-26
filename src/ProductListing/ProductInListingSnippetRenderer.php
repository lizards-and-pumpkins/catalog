<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Import\Product\ProductDTO;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;

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
        $key = $this->snippetKeyGenerator->getKeyForContext($product->getContext(), [ProductDTO::ID => $product->getId()]);
        $content = json_encode($product);
        return Snippet::create($key, $content);
    }
}
