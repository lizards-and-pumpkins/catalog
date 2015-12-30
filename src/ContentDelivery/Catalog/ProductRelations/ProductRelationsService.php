<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations;

use LizardsAndPumpkins\ContentDelivery\Catalog\ProductJsonService;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\ProductId;

class ProductRelationsService
{
    /**
     * @var ProductRelationsLocator
     */
    private $productRelationsLocator;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var ProductJsonService
     */
    private $productJsonService;

    public function __construct(
        ProductRelationsLocator $productRelationsLocator,
        ProductJsonService $productJsonService,
        Context $context
    ) {
        $this->productRelationsLocator = $productRelationsLocator;
        $this->productJsonService = $productJsonService;
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
        return $this->productJsonService->get(...$productIds);
    }
}
