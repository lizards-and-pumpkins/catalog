<?php

namespace Brera\DataPool\SearchEngine;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;

class SearchCriterion implements SearchCriteria, \JsonSerializable
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
     * @param string $fieldValue
     * @param string $operation
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

        if (!in_array($operation, ['=', '!=', '>', '>=', '<', '<='])) {
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
     * @param SearchDocument $searchDocument
     * @return bool
     */
    public function matches(SearchDocument $searchDocument)
    {
        foreach ($searchDocument->getFieldsCollection()->getFields() as $searchDocumentField) {
            if ($searchDocumentField->getKey() !== $this->fieldName) {
                continue;
            }

            $matches = false;
            switch ($this->operation) {
                case '=':
                    $matches = $searchDocumentField->getValue() == $this->fieldValue;
                    break;
                case '!=':
                    $matches = $searchDocumentField->getValue() != $this->fieldValue;
                    break;
                case '>':
                    $matches = $searchDocumentField->getValue() > $this->fieldValue;
                    break;
                case '>=':
                    $matches = $searchDocumentField->getValue() >= $this->fieldValue;
                    break;
                case '<':
                    $matches = $searchDocumentField->getValue() < $this->fieldValue;
                    break;
                case '<=':
                    $matches = $searchDocumentField->getValue() <= $this->fieldValue;
            }

            if (true === $matches) {
                return true;
            }
        }

        return false;
    }
}
