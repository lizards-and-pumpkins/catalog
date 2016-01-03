<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projection\Catalog\InternalToPublicProductJsonData;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\SnippetList;

class ProductInListingSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_in_listing';

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;
    
    /**
     * @var InternalToPublicProductJsonData
     */
    private $internalToPublicProductJsonData;

    public function __construct(
        SnippetKeyGenerator $snippetKeyGenerator,
        InternalToPublicProductJsonData $internalToPublicProductJsonData
    ) {
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->internalToPublicProductJsonData = $internalToPublicProductJsonData;
    }

    /**
     * @param mixed $projectionSourceData
     * @return SnippetList
     */
    public function render($projectionSourceData)
    {
        if (!($projectionSourceData instanceof ProductView)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be a ProductView instance.');
        }

        $snippet = $this->getProductInListingSnippet($projectionSourceData);

        return new SnippetList($snippet);
    }

    /**
     * @param ProductView $product
     * @return Snippet
     */
    private function getProductInListingSnippet(ProductView $product)
    {
        $key = $this->snippetKeyGenerator->getKeyForContext($product->getContext(), [Product::ID => $product->getId()]);
        $internalJson = json_encode($product);
        $publicJson = $this->internalToPublicProductJsonData->transformProduct(json_decode($internalJson, true));
        return Snippet::create($key, json_encode($publicJson));
    }
}
