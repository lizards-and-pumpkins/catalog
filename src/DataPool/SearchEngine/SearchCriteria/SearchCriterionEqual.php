<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

class SearchCriterionEqual implements SearchCriteria
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var mixed
     */
    private $fieldValue;

    /**
     * @param string $fieldName
     * @param mixed $fieldValue
     */
    public function __construct(string $fieldName, $fieldValue)
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'fieldName' => $this->fieldName,
            'fieldValue' => $this->fieldValue,
            'operation' => 'Equal'
        ];
    }
}
