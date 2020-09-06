<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation;

use LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\Exception\InvalidSearchEngineOperationDataSetException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;

class IntegrationTestSearchEnginePrimitiveOperator
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
     * @param mixed[] $dataSet
     */
    public function __construct(array $dataSet)
    {
        $this->validateDataSet($dataSet);

        $this->fieldName = $dataSet['fieldName'];
        $this->fieldValue = $dataSet['fieldValue'];
    }

    public function matches(SearchDocument $searchDocument, \Closure $matchesClosure) : bool
    {
        foreach ($searchDocument->getFieldsCollection() as $searchDocumentField) {
            if ($searchDocumentField->getKey() !== $this->fieldName) {
                continue;
            }

            if ($this->hasValueMatchingOneOfFieldValues($searchDocumentField, $matchesClosure)) {
                return true;
            }
        }

        return false;
    }

    private function hasValueMatchingOneOfFieldValues(
        SearchDocumentField $searchDocumentField,
        \Closure $matchesClosure
    ) : bool {
        foreach ($searchDocumentField->getValues() as $value) {
            if (call_user_func($matchesClosure, $value, $this->fieldValue)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed[] $dataSet
     */
    private function validateDataSet(array $dataSet): void
    {
        if (! array_key_exists('fieldName', $dataSet)) {
            throw new InvalidSearchEngineOperationDataSetException(
                'Search engine operation data set array does not contain "fieldName" element.'
            );
        }

        if (! is_string($dataSet['fieldName'])) {
            throw new InvalidSearchEngineOperationDataSetException(
                'Search engine operation field name must be a string.'
            );
        }

        if (trim($dataSet['fieldName']) === '') {
            throw new InvalidSearchEngineOperationDataSetException(
                'Search engine operation field name must not be empty.'
            );
        }

        if (! array_key_exists('fieldValue', $dataSet)) {
            throw new InvalidSearchEngineOperationDataSetException(
                'Search engine operation data set array does not contain "fieldValue" element.'
            );
        }
    }
}
