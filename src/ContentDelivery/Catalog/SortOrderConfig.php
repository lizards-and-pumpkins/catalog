<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Product\AttributeCode;

class SortOrderConfig implements \JsonSerializable
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
     * @var bool
     */
    private $isSelected;

    /**
     * @param AttributeCode $attributeCode
     * @param SortOrderDirection $selectedDirection
     * @param bool $isSelected
     */
    private function __construct(AttributeCode $attributeCode, SortOrderDirection $selectedDirection, $isSelected)
    {
        $this->attributeCode = $attributeCode;
        $this->selectedDirection = $selectedDirection;
        $this->isSelected = $isSelected;
    }

    /**
     * @param AttributeCode $attributeCode
     * @param SortOrderDirection $selectedDirection
     * @return SortOrderConfig
     */
    public static function create(AttributeCode $attributeCode, SortOrderDirection $selectedDirection)
    {
        return new self($attributeCode, $selectedDirection, false);
    }

    /**
     * @param AttributeCode $attributeCode
     * @param SortOrderDirection $selectedDirection
     * @return SortOrderConfig
     */
    public static function createSelected(AttributeCode $attributeCode, SortOrderDirection $selectedDirection)
    {
        return new self($attributeCode, $selectedDirection, true);
    }

    /**
     * @return AttributeCode
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }

    /**
     * @return SortOrderDirection
     */
    public function getSelectedDirection()
    {
        return $this->selectedDirection;
    }

    /**
     * @return bool
     */
    public function isSelected()
    {
        return $this->isSelected;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            'code' => (string) $this->attributeCode,
            'selectedDirection' => $this->selectedDirection->getDirection(),
            'selected' => $this->isSelected
        ];
    }
}
