<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\SnippetKeyGenerator;

class ProductRelationsService
{
    /**
     * @var ProductRelationsLocator
     */
    private $productRelationsLocator;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var SnippetKeyGenerator
     */
    private $productJsonSnippetKeyGenerator;

    /**
     * @var Context
     */
    private $context;

    public function __construct(
        ProductRelationsLocator $productRelationsLocator,
        DataPoolReader $dataPoolReader,
        SnippetKeyGenerator $productJsonSnippetKeyGenerator,
        Context $context
    ) {
        $this->productRelationsLocator = $productRelationsLocator;
        $this->dataPoolReader = $dataPoolReader;
        $this->productJsonSnippetKeyGenerator = $productJsonSnippetKeyGenerator;
        $this->context = $context;
    }

    /**
     * @param ProductRelationTypeCode $productRelationTypeCode
     * @param ProductId $productId
     * @return array[]
     */
    public function getRelatedProductData(
        ProductRelationTypeCode $productRelationTypeCode,
        ProductId $productId
    ) {
        $productRelations = $this->productRelationsLocator->locate($productRelationTypeCode);
        $relatedProductIds = $productRelations->getById($productId);

        return count($relatedProductIds) > 0 ?
            $this->getProductDataByProductIds($relatedProductIds) :
            [];
    }

    /**
     * @param ProductId[] $productIds
     * @return array[]
     */
    private function getProductDataByProductIds(array $productIds)
    {
        $productJsonSnippets = $this->dataPoolReader->getSnippets($this->getProductJsonSnippetKeys($productIds));
        
        return array_map([$this, 'decodeProductJsonSnippet'], $productJsonSnippets);
    }

    /**
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getProductJsonSnippetKeys(array $productIds)
    {
        return array_map(function (ProductId $productId) {
            return $this->productJsonSnippetKeyGenerator->getKeyForContext($this->context, [Product::ID => $productId]);
        }, $productIds);
    }

    /**
     * @param string $productJson
     * @return mixed[]
     */
    private function decodeProductJsonSnippet($productJson)
    {
        return json_decode($productJson, true);
    }
}
