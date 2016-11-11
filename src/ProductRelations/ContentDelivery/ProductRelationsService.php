<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Import\Product\ProductId;

class ProductRelationsService
{
    /**
     * @var ProductRelationsLocator
     */
    private $productRelationsLocator;

    /**
     * @var ProductJsonService
     */
    private $productJsonService;

    public function __construct(
        ProductRelationsLocator $productRelationsLocator,
        ProductJsonService $productJsonService
    ) {
        $this->productRelationsLocator = $productRelationsLocator;
        $this->productJsonService = $productJsonService;
    }

    /**
     * @param ProductRelationTypeCode $productRelationTypeCode
     * @param ProductId $productId
     * @return array[]
     */
    public function getRelatedProductData(
        ProductRelationTypeCode $productRelationTypeCode,
        ProductId $productId
    ) : array {
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
    private function getProductDataByProductIds(array $productIds) : array
    {
        return $this->productJsonService->get(...$productIds);
    }
}
