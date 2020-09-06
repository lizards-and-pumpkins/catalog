<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation;

use LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\Exception\InvalidSearchEngineOperationDataSetException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;

class IntegrationTestSearchEngineOperationFullText implements IntegrationTestSearchEngineOperation
{
    /**
     * @var string
     */
    private $fieldValue;

    /**
     * @param mixed[] $dataSet
     */
    public function __construct(array $dataSet)
    {
        $this->validateDataSet($dataSet);

        $this->fieldValue = $dataSet['fieldValue'];
    }

    public function matches(SearchDocument $searchDocument) : bool
    {
        foreach ($searchDocument->getFieldsCollection() as $searchDocumentField) {
            if ($this->hasValueMatchingOneOfFieldValues($searchDocumentField)) {
                return true;
            }
        }

        return false;
    }

    private function hasValueMatchingOneOfFieldValues(SearchDocumentField $searchDocumentField) : bool
    {
        foreach ($searchDocumentField->getValues() as $value) {
            if (is_string($value) && stripos($value, $this->fieldValue) !== false) {
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
        if (! array_key_exists('fieldValue', $dataSet)) {
            throw new InvalidSearchEngineOperationDataSetException(
                'Search engine operation data set array does not contain "fieldValue" element.'
            );
        }

        if (! is_string($dataSet['fieldValue'])) {
            throw new InvalidSearchEngineOperationDataSetException(
                'Search engine operation field value must be a string.'
            );
        }
    }
}
