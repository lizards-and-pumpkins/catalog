<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;

abstract class SearchCriterion implements SearchCriteria, \JsonSerializable
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
     * @param string $fieldName
     * @param string $fieldValue
     */
    private function __construct($fieldName, $fieldValue)
    {
        $this->fieldName = $fieldName;
        $this->fieldValue = $fieldValue;
    }

    /**
     * @param string $fieldName
     * @param string $fieldValue
     * @return SearchCriterion
     */
    public static function create($fieldName, $fieldValue)
    {
        if (!is_string($fieldName)) {
            throw new \InvalidArgumentException('Criterion field name should be a string');
        }

        if (!is_string($fieldValue)) {
            throw new \InvalidArgumentException('Criterion field value should be a string');
        }

        $className = get_called_class();

        return new $className($fieldName, $fieldValue);
    }

    /**
     * @return string[]
     */
    public function jsonSerialize()
    {
        return [
            'fieldName' => $this->fieldName,
            'fieldValue' => $this->fieldValue,
            'operation' => $this->extractOperationNameFromClassName()
        ];
    }

    /**
     * @param SearchDocument $searchDocument
     * @return bool
     */
    public function matches(SearchDocument $searchDocument)
    {
        /** @var SearchDocumentField $searchDocumentField */
        foreach ($searchDocument->getFieldsCollection() as $searchDocumentField) {
            if ($searchDocumentField->getKey() !== $this->fieldName) {
                continue;
            }

            if ($this->hasValueMatchingOneOfFieldValues($searchDocumentField)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param SearchDocumentField $searchDocumentField
     * @return bool
     */
    private function hasValueMatchingOneOfFieldValues(SearchDocumentField $searchDocumentField)
    {
        foreach ($searchDocumentField->getValues() as $value) {
            if ($this->hasValueMatchingOperator($value, $this->fieldValue)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $searchDocumentFieldValue
     * @param string $criterionValue
     * @return bool
     */
    abstract protected function hasValueMatchingOperator($searchDocumentFieldValue, $criterionValue);

    /**
     * @return string
     */
    private function extractOperationNameFromClassName()
    {
        return preg_replace('/.*\\SearchCriterion/', '', get_called_class());
    }
}
