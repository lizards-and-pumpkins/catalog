<?php

namespace Brera\DataPool\SearchEngine;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField;

class SearchCriterion implements \JsonSerializable
{
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

        if (!in_array($operation, ['eq', 'neq', 'gt', 'gte', 'lt', 'lte'])) {
            throw new \InvalidArgumentException('Invalid criterion operation');
        }

        return new self($fieldName, $fieldValue, $operation);
    }

    /**
     * @return string[]
     */
    public function jsonSerialize()
    {
        return [
            'fieldName'     => $this->fieldName,
            'fieldValue'    => $this->fieldValue,
            'operation'     => $this->operation
        ];
    }

    /**
     * @param SearchDocumentField $searchDocumentField
     * @return bool
     */
    public function matches(SearchDocumentField $searchDocumentField)
    {
        if ($searchDocumentField->getKey() !== $this->fieldName) {
            return false;
        }

        switch ($this->operation) {
            case 'eq':
                return $searchDocumentField->getValue() == $this->fieldValue;
            case 'neq':
                return $searchDocumentField->getValue() != $this->fieldValue;
            case 'gt':
                return $searchDocumentField->getValue() > $this->fieldValue;
            case 'gte';
                return $searchDocumentField->getValue() >= $this->fieldValue;
            case 'lt':
                return $searchDocumentField->getValue() < $this->fieldValue;
            case 'lte':
                return $searchDocumentField->getValue() <= $this->fieldValue;
        }

        return false;
    }
}
