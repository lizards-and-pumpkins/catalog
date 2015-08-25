<?php

namespace Brera\Product\Block;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\Renderer\Block;
use Brera\Renderer\InvalidDataObjectException;

class FilterNavigationBlock extends Block
{
    /**
     * @var array[]
     */
    private $lazyLoadedFilters;

    /**
     * @var string[]
     */
    private $filterNavigationAttributeCodes = [];

    /**
     * @return array[]
     */
    public function getFilters()
    {
        if (null === $this->lazyLoadedFilters) {
            $dataObject = $this->getDataObject();
            $this->validateDataObject($dataObject);

            /** @var SearchDocumentCollection $searchDocumentsCollection */
            $searchDocumentsCollection = $dataObject['search_document_collection'];
            $searchDocuments = $searchDocumentsCollection->getDocuments();

            $this->filterNavigationAttributeCodes = $dataObject['filter_navigation_attribute_codes'];
            $this->lazyLoadedFilters = array_reduce($searchDocuments, [$this, 'addSearchDocumentFieldsToFilter'], []);
        }

        return $this->lazyLoadedFilters;
    }

    /**
     * @param string[] $filters
     * @param SearchDocument $searchDocument
     * @return \string[]
     */
    private function addSearchDocumentFieldsToFilter(array $filters, SearchDocument $searchDocument)
    {
        $searchDocumentFieldsCollection = $searchDocument->getFieldsCollection();
        $searchDocumentFields = $searchDocumentFieldsCollection->getFields();

        foreach ($searchDocumentFields as $searchDocumentField) {
            $searchDocumentFieldKey = $searchDocumentField->getKey();
            $searchDocumentFieldValue = $searchDocumentField->getValue();

            if (!in_array($searchDocumentFieldKey, $this->filterNavigationAttributeCodes)) {
                continue;
            }

            if (!isset($filters[$searchDocumentFieldKey])) {
                $filters[$searchDocumentFieldKey] = [];
            }

            if (!isset($filters[$searchDocumentFieldKey][$searchDocumentFieldValue])) {
                $filters[$searchDocumentFieldKey][$searchDocumentFieldValue] = 0;
            }

            $filters[$searchDocumentFieldKey][$searchDocumentFieldValue]++;
        }

        return $filters;
    }

    /**
     * @param mixed $dataObject
     */
    private function validateDataObject($dataObject)
    {
        if (!is_array($dataObject)) {
            throw new InvalidDataObjectException(
                sprintf('Data object must be an array, got "%s".', gettype($dataObject))
            );
        }

        if (!isset($dataObject['search_document_collection'])) {
            throw new InvalidDataObjectException('Data object array must have "search_document_collection" node');
        }

        if (!($dataObject['search_document_collection'] instanceof SearchDocumentCollection)) {
            throw new InvalidDataObjectException(sprintf(
                '"search_document_collection" node must be instance of SearchDocumentCollection, got %s.',
                $this->getVariableType($dataObject['search_document_collection'])
            ));
        }

        if (!isset($dataObject['filter_navigation_attribute_codes'])) {
            throw new InvalidDataObjectException(
                'Data object array must have "filter_navigation_attribute_codes" node'
            );
        }

        if (!is_array($dataObject['filter_navigation_attribute_codes'])) {
            throw new InvalidDataObjectException(sprintf(
                '"filter_navigation_attribute_codes" node must be an array, got %s.',
                $this->getVariableType($dataObject['filter_navigation_attribute_codes'])
            ));
        }
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private function getVariableType($variable)
    {
        return 'object' !== gettype($variable) ? gettype($variable) : get_class($variable);
    }
}
