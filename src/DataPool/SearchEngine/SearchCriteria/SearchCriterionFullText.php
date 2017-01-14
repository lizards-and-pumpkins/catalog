<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

class SearchCriterionFullText implements SearchCriteria
{
    /**
     * @var string
     */
    private $fieldValue;

    public function __construct(string $fieldValue)
    {
        $this->fieldValue = $fieldValue;
    }

    /**
     * @return array[]
     */
    function jsonSerialize() : array
    {
        return $this->toArray();
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'fieldName' => '',
            'fieldValue' => $this->fieldValue,
            'operation' => 'FullText',
        ];
    }
}
