<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\Query;

use LizardsAndPumpkins\Import\Product\AttributeCode;

class SortBy implements \JsonSerializable
{
    /**
     * @var AttributeCode
     */
    private $attributeCode;

    /**
     * @var SortDirection
     */
    private $selectedDirection;

    public function __construct(AttributeCode $attributeCode, SortDirection $selectedDirection)
    {
        $this->attributeCode = $attributeCode;
        $this->selectedDirection = $selectedDirection;
    }

    public function getAttributeCode() : AttributeCode
    {
        return $this->attributeCode;
    }

    public function getSelectedDirection() : SortDirection
    {
        return $this->selectedDirection;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize() : array
    {
        return [
            'code' => (string) $this->attributeCode,
            'selectedDirection' => (string) $this->selectedDirection,
        ];
    }
}
