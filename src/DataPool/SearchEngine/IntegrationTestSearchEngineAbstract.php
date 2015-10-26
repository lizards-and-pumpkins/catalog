<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
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
     * @return SearchCriteriaBuilder
     */
    abstract protected function getSearchCriteriaBuilder();

    /**
     * @inheritdoc
     */
    final public function getSearchDocumentsMatchingCriteria(
        SearchCriteria $originalCriteria,
        array $selectedFilters,
        Context $context,
        array $facetFiltersConfig,
        $rowsPerPage,
        $pageNumber
    ) {
        $criteria = $this->applyFiltersToSelectionCriteria($originalCriteria, $selectedFilters);
        $matchingDocuments = array_filter(
            $this->getSearchDocuments(),
            function (SearchDocument $searchDocument) use ($criteria, $context) {
                return $criteria->matches($searchDocument) && $context->isSubsetOf($searchDocument->getContext());
            }
        );

        return $this->createSearchEngineResponse($facetFiltersConfig, $matchingDocuments, $rowsPerPage, $pageNumber);
    }

    /**
     * @param SearchCriteria $originalCriteria
     * @param array[] $filters
     * @return SearchCriteria
     */
    private function applyFiltersToSelectionCriteria(SearchCriteria $originalCriteria, array $filters)
    {
        $filtersCriteriaArray = [];

        foreach ($filters as $filterCode => $filterOptionValues) {
            if (empty($filterOptionValues)) {
                continue;
            }

            $optionValuesCriteriaArray = $this->createOptionValuesCriteriaArray($filterCode, $filterOptionValues);
            $filterCriteria = CompositeSearchCriterion::createOr(...$optionValuesCriteriaArray);
            $filtersCriteriaArray[] = $filterCriteria;
        }

        if (empty($filtersCriteriaArray)) {
            return $originalCriteria;
        }

        $filtersCriteriaArray[] = $originalCriteria;
        return CompositeSearchCriterion::createAnd(...$filtersCriteriaArray);
    }

    /**
     * @param string $filterCode
     * @param string[] $filterOptionValues
     * @return SearchCriteria[]
     */
    private function createOptionValuesCriteriaArray($filterCode, array $filterOptionValues)
    {
        return array_map(function ($filterOptionValue) use ($filterCode) {
            return $this->getSearchCriteriaBuilder()->fromRequestParameter($filterCode, $filterOptionValue);
        }, $filterOptionValues);
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
     * @param string[] $facetFiltersConfig
     * @param SearchDocument[] $searchDocuments
     * @return SearchEngineFacetFieldCollection
     */
    private function createFacetFieldsCollectionFromSearchDocuments(
        array $facetFiltersConfig,
        SearchDocument ...$searchDocuments
    ) {
        $facetFieldCodes = array_keys($facetFiltersConfig);
        $attributeCounts = $this->createAttributeValueCountArrayFromSearchDocuments(
            $facetFieldCodes,
            ...$searchDocuments
        );

        $facetFields = array_map(function ($attributeCode, $attributeValues) use ($facetFiltersConfig) {
            $facetFieldValues = $this->getFacetFieldValuesFromAttributeValues(
                $attributeCode,
                $attributeValues,
                $facetFiltersConfig
            );
            return new SearchEngineFacetField(AttributeCode::fromString($attributeCode), ...$facetFieldValues);
        }, array_keys($attributeCounts), $attributeCounts);

        return new SearchEngineFacetFieldCollection(...$facetFields);
    }

    /**
     * @param string[] $facetFieldCodes
     * @param SearchDocument[] $searchDocuments
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
            if ((string)$currentAttributeCode !== $attributeCode) {
                $newValues[$currentAttributeCode] = $currentAttributeValues;
                continue;
            }

            $newAttributeValues = [];
            foreach ($currentAttributeValues as $currentValueArray) {
                if ($currentValueArray['value'] === $newValue) {
                    $currentValueArray['count'] ++;
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
     * @param string[] $facetFiltersConfig
     * @param SearchDocument[] $searchDocuments
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @return SearchEngineResponse
     */
    private function createSearchEngineResponse(
        array $facetFiltersConfig,
        array $searchDocuments,
        $rowsPerPage,
        $pageNumber
    ) {
        $facetFieldCollection = $this->createFacetFieldsCollectionFromSearchDocuments(
            $facetFiltersConfig,
            ...array_values($searchDocuments)
        );

        $totalNumberOfResults = count($searchDocuments);
        $currentPageDocuments = array_slice($searchDocuments, $pageNumber * $rowsPerPage, $rowsPerPage);
        $documentCollection = new SearchDocumentCollection(...array_values($currentPageDocuments));

        return new SearchEngineResponse($documentCollection, $facetFieldCollection, $totalNumberOfResults);
    }

    /**
     * @param string $attributeCode
     * @param array[] $attributeValues
     * @param array[] $facetFiltersConfig
     * @return SearchEngineFacetFieldValueCount[]
     */
    private function getFacetFieldValuesFromAttributeValues(
        $attributeCode,
        array $attributeValues,
        array $facetFiltersConfig
    ) {
        $configuredRanges = $facetFiltersConfig[$attributeCode];

        if (!empty($configuredRanges)) {
            return $this->createRangedFacetFieldFromAttributeValues($attributeValues, $configuredRanges);
        }

        return $this->createDefaultFacetFieldFromAttributeValues($attributeValues);
    }

    /**
     * @param array[] $attributeValues
     * @return SearchEngineFacetFieldValueCount[]
     */
    private function createDefaultFacetFieldFromAttributeValues(array $attributeValues)
    {
        return array_map(function ($valueCounts) {
            return SearchEngineFacetFieldValueCount::create((string)$valueCounts['value'],
                $valueCounts['count']);
        }, $attributeValues);
    }

    /**
     * @param array[] $attributeValues
     * @param array[] $configuredRanges
     * @return SearchEngineFacetFieldValueCount[]
     */
    private function createRangedFacetFieldFromAttributeValues(array $attributeValues, $configuredRanges)
    {
        return array_reduce($configuredRanges, function ($carry, array $range) use ($attributeValues) {
            $rangeCount = $this->sumAttributeValuesCountsInRange($range, $attributeValues);
            if ($rangeCount > 0) {
                $rangeCode = $range['from'] . SearchEngine::RANGE_DELIMITER . $range['to'];
                $carry[] = SearchEngineFacetFieldValueCount::create($rangeCode, $rangeCount);
            }

            return $carry;
        }, []);
    }

    /**
     * @param mixed[] $range
     * @param array[] $attributeValues
     * @return int
     */
    private function sumAttributeValuesCountsInRange(array $range, $attributeValues)
    {
        return array_reduce($attributeValues, function ($carry, array $valueCounts) use ($range) {
            if ((SearchEngine::RANGE_WILDCARD === $range['from'] || $valueCounts['value'] >= $range['from']) &&
                (SearchEngine::RANGE_WILDCARD === $range['to'] || $valueCounts['value'] <= $range['to'])
            ) {
                $carry += $valueCounts['count'];
            }

            return $carry;
        }, 0);
    }
}
