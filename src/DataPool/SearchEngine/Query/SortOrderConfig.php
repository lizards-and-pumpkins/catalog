<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\Query;

use LizardsAndPumpkins\Import\Product\AttributeCode;

class SortOrderConfig implements \JsonSerializable
{
    /**
     * @var AttributeCode
     */
    private $attributeCode;

    /**
     * @var SortOrderDirection
     */
    private $selectedDirection;

    /**
     * @var bool
     */
    private $isSelected;

    private function __construct(AttributeCode $attributeCode, SortOrderDirection $selectedDirection, bool $isSelected)
    {
        $this->attributeCode = $attributeCode;
        $this->selectedDirection = $selectedDirection;
        $this->isSelected = $isSelected;
    }

    public static function create(AttributeCode $attributeCode, SortOrderDirection $selectedDirection) : SortOrderConfig
    {
        return new self($attributeCode, $selectedDirection, false);
    }

    public static function createSelected(
        AttributeCode $attributeCode,
        SortOrderDirection $selectedDirection
    ) : SortOrderConfig {
        return new self($attributeCode, $selectedDirection, true);
    }

    public function getAttributeCode() : AttributeCode
    {
        return $this->attributeCode;
    }

    public function getSelectedDirection() : SortOrderDirection
    {
        return $this->selectedDirection;
    }

    public function isSelected() : bool
    {
        return $this->isSelected;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize() : array
    {
        return [
            'code' => (string) $this->attributeCode,
            'selectedDirection' => (string) $this->selectedDirection,
            'selected' => $this->isSelected
        ];
    }
}
