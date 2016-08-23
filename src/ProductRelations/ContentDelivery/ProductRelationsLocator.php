<?php

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

    /**
     * @param ProductRelationTypeCode $relationTypeCode
     * @return ProductRelations
     */
    public function locate(ProductRelationTypeCode $relationTypeCode)
    {
        if (!isset($this->factoryMethods[(string) $relationTypeCode])) {
            $message = sprintf('The product relation "%s" is unknown', $relationTypeCode);
            throw new UnknownProductRelationTypeException($message);
        }
        return $this->createProductRelationType($relationTypeCode);
    }

    /**
     * @param ProductRelationTypeCode $relationTypeCode
     * @return ProductRelations
     */
    private function createProductRelationType(ProductRelationTypeCode $relationTypeCode)
    {
        $productRelationType = call_user_func($this->factoryMethods[(string) $relationTypeCode]);
        $this->validateProductRelationType($productRelationType);
        return $productRelationType;
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private function getVariableType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }

    /**
     * @param ProductRelations $productRelationType
     */
    private function validateProductRelationType($productRelationType)
    {
        if (!is_object($productRelationType) || !$productRelationType instanceof ProductRelations) {
            $message = sprintf(
                'Product Relation Type "%s" has to implement the ProductRelationType interface',
                $this->getVariableType($productRelationType)
            );
            throw new InvalidProductRelationTypeException($message);
        }
    }
}
