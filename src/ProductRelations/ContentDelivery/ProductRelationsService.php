<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonServiceBuilder;
use LizardsAndPumpkins\Import\Product\ProductId;

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
     * @var ProductJsonServiceBuilder
     */
    private $productJsonServiceBuilder;

    public function __construct(
        ProductRelationsLocator $productRelationsLocator,
        ProductJsonServiceBuilder $productJsonServiceBuilder,
        Context $context
    ) {
        $this->productRelationsLocator = $productRelationsLocator;
        $this->productJsonServiceBuilder = $productJsonServiceBuilder;
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
        $productJsonServiceBuilder = $this->productJsonServiceBuilder->getForContext($this->context);

        return $productJsonServiceBuilder->get(...$productIds);
    }
}
