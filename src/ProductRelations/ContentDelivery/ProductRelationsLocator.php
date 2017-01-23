<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\ProductRelations\Exception\InvalidProductRelationTypeException;
use LizardsAndPumpkins\ProductRelations\Exception\UnknownProductRelationTypeException;
use LizardsAndPumpkins\ProductRelations\ProductRelations;

class ProductRelationsLocator
{
    /**
     * @var array[]
     */
    private $factoryMethods = [];

    public function register(ProductRelationTypeCode $relationTypeCode, callable $factoryMethod)
    {
        $this->factoryMethods[(string) $relationTypeCode] = $factoryMethod;
    }

    public function locate(ProductRelationTypeCode $relationTypeCode) : ProductRelations
    {
        if (!isset($this->factoryMethods[(string) $relationTypeCode])) {
            $message = sprintf('The product relation "%s" is unknown', $relationTypeCode);
            throw new UnknownProductRelationTypeException($message);
        }
        return $this->createProductRelationType($relationTypeCode);
    }

    private function createProductRelationType(ProductRelationTypeCode $relationTypeCode) : ProductRelations
    {
        $productRelationType = call_user_func($this->factoryMethods[(string) $relationTypeCode]);
        $this->validateProductRelationType($productRelationType);
        return $productRelationType;
    }

    /**
     * @param ProductRelations $productRelationType
     */
    private function validateProductRelationType($productRelationType)
    {
        if (!is_object($productRelationType) || !$productRelationType instanceof ProductRelations) {
            $message = sprintf(
                'Product Relation Type "%s" has to implement the ProductRelationType interface',
                typeof($productRelationType)
            );
            throw new InvalidProductRelationTypeException($message);
        }
    }
}
