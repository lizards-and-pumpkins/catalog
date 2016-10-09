<?php

declare(strict_types=1);

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
        return [
            'fieldName' => $this->fieldName,
            'fieldValue' => $this->fieldValue,
            'operation' => $this->extractOperationNameFromClassName()
        ];
    }

    public function matches(SearchDocument $searchDocument) : bool
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

    private function hasValueMatchingOneOfFieldValues(SearchDocumentField $searchDocumentField) : bool
    {
        foreach ($searchDocumentField->getValues() as $value) {
            if ($this->hasValueMatchingOperator($value, $this->fieldValue)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param mixed $searchDocumentFieldValue
     * @param mixed $criterionValue
     * @return bool
     */
    abstract protected function hasValueMatchingOperator($searchDocumentFieldValue, $criterionValue) : bool;

    private function extractOperationNameFromClassName() : string
    {
        return preg_replace('/.*\\SearchCriterion/', '', get_called_class());
    }
}
