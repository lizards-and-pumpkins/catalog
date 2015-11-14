<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationCollection;
use LizardsAndPumpkins\Product\AttributeCode;

class FacetFilterConfig
{
    /**
     * @var AttributeCode
     */
    private $attributeCode;

    /**
     * @var FacetFieldRangeCollection
     */
    private $rangeCollection;

    /**
     * @var FacetFieldTransformationCollection
     */
    private $transformationCollection;

    public function __construct(
        AttributeCode $attributeCode,
        FacetFieldRangeCollection $rangeCollection,
        FacetFieldTransformationCollection $transformationCollection
    ) {
        $this->attributeCode = $attributeCode;
        $this->transformationCollection = $transformationCollection;
        $this->rangeCollection = $rangeCollection;
    }

    /**
     * @return AttributeCode
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }

    /**
     * @return FacetFieldTransformationCollection
     */
    public function getTransformations()
    {
        return $this->transformationCollection;
    }

    /**
     * @return FacetFieldRangeCollection
     */
    public function getRanges()
    {
        return $this->rangeCollection;
    }
}
