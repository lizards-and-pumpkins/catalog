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
     * @var string[]
     */
    private $directions;

    /**
     * @var string
     */
    private $selectedDirection;

    /**
     * @param AttributeCode $attributeCode
     * @param string[] $directions
     * @param string $selectedDirection
     */
    public function __construct(AttributeCode $attributeCode, array $directions, $selectedDirection)
    {
        $this->attributeCode = $attributeCode;
        $this->directions = $directions;
        $this->selectedDirection = $selectedDirection;
    }

    /**
     * @param AttributeCode $attributeCode
     * @param mixed[] $directions
     * @param string $selectedDirection
     * @return SortOrderConfig
     */
    public static function create(AttributeCode $attributeCode, array $directions, $selectedDirection)
    {
        self::validateSortingDirections($attributeCode, $directions, $selectedDirection);
        return new self($attributeCode, $directions, $selectedDirection);
    }

    /**
     * @return AttributeCode
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }

    /**
     * @return string[]
     */
    public function getDirections()
    {
        return $this->directions;
    }

    /**
     * @param AttributeCode $attributeCode
     * @param mixed[] $directions
     * @param mixed $selectedDirection
     */
    private static function validateSortingDirections(
        AttributeCode $attributeCode,
        array $directions,
        $selectedDirection
    ) {
        array_map(function ($direction) use ($attributeCode) {
            if (SearchEngine::SORT_DIRECTION_ASC !== $direction && SearchEngine::SORT_DIRECTION_DESC !== $direction) {
                throw new InvalidSortingDirectionsException(
                    sprintf('Invalid sorting direction "%s" specified for attribute "%s".', $direction, $attributeCode)
                );
            }
        }, $directions);

        if (!in_array($selectedDirection, $directions)) {
            throw new InvalidSortingDirectionsException(sprintf(
                'Invalid selected sorting direction "%s" specified for attribute "%s".',
                $selectedDirection,
                $attributeCode
            ));
        }

        if (empty($directions)) {
            throw new InvalidSortingDirectionsException(
                sprintf('No sorting directions specified for attribute "%s".', $attributeCode)
            );
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
