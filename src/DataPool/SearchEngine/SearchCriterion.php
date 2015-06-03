<?php

namespace Brera\DataPool\SearchEngine;

class SearchCriterion
{
    const VALID_OPERATIONS = ['eq', 'neq', 'gt', 'gte', 'lt', 'lte'];

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $fieldValue;

    /**
     * @var string
     */
    private $operation;

    /**
     * @param string $fieldName
     * @param $fieldValue
     * @param $operation
     */
    private function __construct($fieldName, $fieldValue, $operation)
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
        $this->operation = $operation;
    }

    /**
     * @param string $fieldName
     * @param string $fieldValue
     * @param string $operation
     * @return SearchCriterion
     */
    public static function create($fieldName, $fieldValue, $operation)
    {
        if (!is_string($fieldName)) {
            throw new \InvalidArgumentException('Criterion field name should be a string');
        }

        if (!is_string($fieldValue)) {
            throw new \InvalidArgumentException('Criterion field value should be a string');
        }

        if (!in_array($operation, self::VALID_OPERATIONS)) {
            throw new \InvalidArgumentException('Invalid criterion operation');
        }

        return new self($fieldName, $fieldValue, $operation);
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getFieldValue()
    {
        return $this->fieldValue;
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }
}
