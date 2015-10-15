<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Utils\Clearable;

abstract class IntegrationTestSearchEngineAbstract implements SearchEngine, Clearable
{
    /**
     * @return SearchDocument[]
     */
    abstract protected function getSearchDocuments();

    /**
     * @inheritdoc
     */
    final public function getSearchDocumentsMatchingCriteria(
        SearchCriteria $criteria,
        Context $context,
        array $facetFields,
        $rowsPerPage,
        $pageNumber
    ) {
        $matchingDocuments = array_filter(
            $this->getSearchDocuments(),
            function (SearchDocument $searchDocument) use ($criteria, $context) {
                return $criteria->matches($searchDocument) && $context->isSubsetOf($searchDocument->getContext());
            }
        );

        return $this->createSearchEngineResponse($facetFields, $matchingDocuments, $rowsPerPage, $pageNumber);
    }

    /**
     * @param SearchDocument $searchDocument
     * @return string
     */
    final protected function getSearchDocumentIdentifier(SearchDocument $searchDocument)
    {
        return $searchDocument->getProductId() . ':' . $searchDocument->getContext()->toString();
    }

    /**
     * @param string[] $facetFieldCodes
     * @param SearchDocument ...$searchDocuments
     * @return SearchEngineFacetFieldCollection
     */
    private function createFacetFieldsCollectionFromSearchDocuments(
        array $facetFieldCodes,
        SearchDocument ...$searchDocuments
    ) {
        $attributeCounts = $this->createAttributeValueCountArrayFromSearchDocuments(
            $facetFieldCodes,
            ...$searchDocuments
        );
        $facetFields = array_map(function ($attributeCode, $attributeValues) {
            $facetFieldValues = $this->getFacetFieldValuesFromAttributeValues($attributeValues);
            return new SearchEngineFacetField(AttributeCode::fromString($attributeCode), ...$facetFieldValues);
        }, array_keys($attributeCounts), $attributeCounts);

        return new SearchEngineFacetFieldCollection(...$facetFields);
    }

    /**
     * @param string[] $facetFieldCodes
     * @param SearchDocument ...$searchDocuments
     * @return array[]
     */
    private function createAttributeValueCountArrayFromSearchDocuments(
        array $facetFieldCodes,
        SearchDocument ...$searchDocuments
    ) {
        return array_reduce($searchDocuments, function ($carry, SearchDocument $document) use ($facetFieldCodes) {
            return $this->addDocumentToCount($carry, $document, $facetFieldCodes);
        }, []);
    }

    /**
     * @param array[] $attributeValuesCount
     * @param SearchDocument $document
     * @param string[] $facetFieldCodes
     * @return array[]
     */
    private function addDocumentToCount(array $attributeValuesCount, SearchDocument $document, array $facetFieldCodes)
    {
        return array_reduce(
            $document->getFieldsCollection()->getFields(),
            function ($carry, SearchDocumentField $documentField) use ($facetFieldCodes) {
                if (!in_array($documentField->getKey(), $facetFieldCodes)) {
                    return $carry;
                }

                return $this->addDocumentFieldValuesToCount($carry, $documentField->getKey(), $documentField);
            },
            $attributeValuesCount
        );
    }

    /**
     * @param array[] $attributeValuesCounts
     * @param string $attributeCode
     * @param SearchDocumentField $documentField
     * @return array[]
     */
    private function addDocumentFieldValuesToCount(
        array $attributeValuesCounts,
        $attributeCode,
        SearchDocumentField $documentField
    ) {
        return array_reduce($documentField->getValues(), function ($carry, $value) use ($attributeCode) {
            return $this->addValueToCount($carry, $attributeCode, $value);
        }, $attributeValuesCounts);
    }

    /**
     * @param array[] $attributeValuesCounts
     * @param string $attributeCode
     * @param string $newValue
     * @return array[]
     */
    private function addValueToCount(array $attributeValuesCounts, $attributeCode, $newValue)
    {
        $valueWasFound = false;
        $newValues = [];

        foreach ($attributeValuesCounts as $currentAttributeCode => $currentAttributeValues) {
            if ((string) $currentAttributeCode !== $attributeCode) {
                $newValues[$currentAttributeCode] = $currentAttributeValues;
                continue;
            }

            $newAttributeValues = [];
            foreach ($currentAttributeValues as $currentValueArray) {
                if ($currentValueArray['value'] === $newValue) {
                    $currentValueArray['count']++;
                    $valueWasFound = true;
                }
                $newAttributeValues[] = $currentValueArray;
            }

            $newValues[$currentAttributeCode] = $newAttributeValues;
        }

        if (false === $valueWasFound) {
            $newValues[$attributeCode][] = ['value' => $newValue, 'count' => 1];
        }

        return $newValues;
    }

    /**
     * @param string[] $facetFields
     * @param SearchDocument[] $searchDocuments
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @return SearchEngineResponse
     */
    private function createSearchEngineResponse(array $facetFields, array $searchDocuments, $rowsPerPage, $pageNumber)
    {
        $facetFieldCollection = $this->createFacetFieldsCollectionFromSearchDocuments(
            $facetFields,
            ...array_values($searchDocuments)
        );

        $totalNumberOfResults = count($searchDocuments);
        $currentPageDocuments = array_slice($searchDocuments, $pageNumber * $rowsPerPage, $rowsPerPage);
        $documentCollection = new SearchDocumentCollection(...array_values($currentPageDocuments));

        return new SearchEngineResponse($documentCollection, $facetFieldCollection, $totalNumberOfResults);
    }

    /**
     * @param int[] $attributeValues
     * @return SearchEngineFacetFieldValueCount[]
     */
    private function getFacetFieldValuesFromAttributeValues($attributeValues)
    {
        return array_map(function ($valueCounts) {
            return SearchEngineFacetFieldValueCount::create((string) $valueCounts['value'], $valueCounts['count']);
        }, $attributeValues);
    }
}
