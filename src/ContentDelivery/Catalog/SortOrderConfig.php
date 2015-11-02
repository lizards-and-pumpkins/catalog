<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\InvalidSortingDirectionsException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\Product\AttributeCode;

class SortOrderConfig
{
    /**
     * @var AttributeCode
     */
    private $attributeCode;

    /**
     * @var string
     */
    private $selectedDirection;

    /**
     * @param AttributeCode $attributeCode
     * @param string $selectedDirection
     */
    public function __construct(AttributeCode $attributeCode, $selectedDirection)
    {
        $this->attributeCode = $attributeCode;
        $this->selectedDirection = $selectedDirection;
    }

    /**
     * @param AttributeCode $attributeCode
     * @param string $selectedDirection
     * @return SortOrderConfig
     */
    public static function create(AttributeCode $attributeCode, $selectedDirection)
    {
        self::validateSortingDirections($attributeCode, $selectedDirection);
        return new self($attributeCode, $selectedDirection);
    }

    /**
     * @return AttributeCode
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }

    /**
     * @param AttributeCode $attributeCode
     * @param mixed $direction
     */
    private static function validateSortingDirections(AttributeCode $attributeCode, $direction)
    {
        if (SearchEngine::SORT_DIRECTION_ASC !== $direction && SearchEngine::SORT_DIRECTION_DESC !== $direction) {
            throw new InvalidSortingDirectionsException(sprintf(
                'Invalid selected sorting direction "%s" specified for attribute "%s".',
                $direction,
                $attributeCode
            ));
        }
    }

    /**
     * @return string
     */
    public function getSelectedDirection()
    {
        return $this->selectedDirection;
    }
}
