<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection;
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
     * {@inheritdoc}
     */
    final public function getSearchDocumentsMatchingCriteria(
        SearchCriteria $originalCriteria,
        array $filterSelection,
        Context $context,
        FacetFilterRequest $facetFilterRequest,
        $rowsPerPage,
        $pageNumber,
        SortOrderConfig $sortOrderConfig
    ) {
        $selectedFilters = array_filter($filterSelection);
        $criteria = $this->applyFiltersToSelectionCriteria($originalCriteria, $selectedFilters);

        $allDocuments = $this->getSearchDocuments();
        $matchingDocuments = $this->filterDocumentsMatchingCriteria($allDocuments, $criteria, $context);

        $facetFieldCollection = $this->createFacetFieldCollection(
            $originalCriteria,
            $context,
            $facetFilterRequest,
            $selectedFilters,
            $matchingDocuments,
            $allDocuments
        );

        $sortedDocuments = $this->getSortedDocuments($sortOrderConfig, ...array_values($matchingDocuments));

        return $this->createSearchEngineResponse($facetFieldCollection, $sortedDocuments, $rowsPerPage, $pageNumber);
    }

    /**
     * @param SearchCriteria $originalCriteria
     * @param array[] $filters
     * @return SearchCriteria
     */
    private function applyFiltersToSelectionCriteria(SearchCriteria $originalCriteria, array $filters)
    {
        if (empty($filters)) {
            return $originalCriteria;
        }

        $filtersCriteriaArray = $this->createSearchEngineCriteriaForFilters($filters);

        return CompositeSearchCriterion::createAnd($originalCriteria, ...$filtersCriteriaArray);
    }

    /**
     * @param array[] $filters
     * @return CompositeSearchCriterion[]
     */
    private function createSearchEngineCriteriaForFilters(array $filters)
    {
        return array_map(function ($filterCode, $filterOptionValues) {
            $optionValuesCriteriaArray = $this->createOptionValuesCriteriaArray($filterCode, $filterOptionValues);
            return CompositeSearchCriterion::createOr(...$optionValuesCriteriaArray);
        }, array_keys($filters), $filters);
    }

    /**
     * @param SearchCriteria $originalCriteria
     * @param Context $context
     * @param FacetFilterRequest $facetFilterRequest
     * @param array[] $selectedFilters
     * @param SearchDocument[] $matchingDocuments
     * @param SearchDocument[] $allDocuments
     * @return FacetFieldCollection
     */
    private function createFacetFieldCollection(
        SearchCriteria $originalCriteria,
        Context $context,
        FacetFilterRequest $facetFilterRequest,
        $selectedFilters,
        $matchingDocuments,
        $allDocuments
    ) {
        $facetFilterAttributeCodeStrings = $facetFilterRequest->getAttributeCodeStrings();
        $selectedFilterCodes = array_keys($selectedFilters);
        $unselectedFilterCodes = array_diff($facetFilterAttributeCodeStrings, $selectedFilterCodes);

        $facetFieldsForUnselectedFilters = $this->createFacetFieldsFromSearchDocuments(
            $unselectedFilterCodes,
            $facetFilterRequest,
            ...array_values($matchingDocuments)
        );

        $facetFieldsForSelectedFilters = $this->getSelectedFiltersFacetValuesWithSiblings(
            $originalCriteria,
            $context,
            $selectedFilters,
            $allDocuments,
            $facetFilterRequest
        );

        $facetFieldArray = array_merge($facetFieldsForSelectedFilters, $facetFieldsForUnselectedFilters);

        return new FacetFieldCollection(...$facetFieldArray);
    }

    /**
     * @param SearchCriteria $originalCriteria
     * @param Context $context
     * @param array[] $selectedFilters
     * @param SearchDocument[] $allDocuments
     * @param FacetFilterRequest $facetFilterRequest
     * @return FacetField[]
     */
    private function getSelectedFiltersFacetValuesWithSiblings(
        SearchCriteria $originalCriteria,
        Context $context,
        array $selectedFilters,
        array $allDocuments,
        FacetFilterRequest $facetFilterRequest
    ) {
        $facetFieldsForSelectedFilters = [];

        foreach (array_keys($selectedFilters) as $filterCode) {
            $selectedFiltersExceptCurrentOne = array_diff_key($selectedFilters, [$filterCode => []]);

            $criteria = $this->applyFiltersToSelectionCriteria($originalCriteria, $selectedFiltersExceptCurrentOne);
            $matchingDocuments = $this->filterDocumentsMatchingCriteria($allDocuments, $criteria, $context);

            $facetFields = $this->createFacetFieldsFromSearchDocuments(
                [$filterCode],
                $facetFilterRequest,
                ...array_values($matchingDocuments)
            );

            $facetFieldsForSelectedFilters[] = $facetFields[0];
        }

        return $facetFieldsForSelectedFilters;
    }

    /**
     * @param string $filterCode
     * @param string[] $filterOptionValues
     * @return SearchCriteria[]
     */
    private function createOptionValuesCriteriaArray($filterCode, array $filterOptionValues)
    {
        return array_map(function ($filterOptionValue) use ($filterCode) {
            return $this->getSearchCriteriaBuilder()->fromFieldNameAndValue($filterCode, $filterOptionValue);
        }, $filterOptionValues);
    }

    /**
     * @param SearchDocument $searchDocument
     * @return string
     */
    final protected function getSearchDocumentIdentifier(SearchDocument $searchDocument)
    {
        return $searchDocument->getProductId() . ':' . $searchDocument->getContext();
    }

    /**
     * @param string[] $fieldCodes
     * @param FacetFilterRequest $facetFilterRequest
     * @param SearchDocument[] $searchDocuments
     * @return FacetField[]
     */
    private function createFacetFieldsFromSearchDocuments(
        array $fieldCodes,
        FacetFilterRequest $facetFilterRequest,
        SearchDocument ...$searchDocuments
    ) {
        $attributeCounts = $this->createAttributeValueCountArrayFromSearchDocuments($fieldCodes, ...$searchDocuments);

        return array_reduce(
            $facetFilterRequest->getFields(),
            function (array $carry, FacetFilterRequestField $field) use ($attributeCounts, $fieldCodes) {
                $attributeCode = $field->getAttributeCode();

                if (!in_array((string)$attributeCode, $fieldCodes)) {
                    return $carry;
                }

                $facetFieldValues = $this->getFacetFieldValuesFromAttributeValues(
                    $attributeCounts[(string) $attributeCode],
                    $field
                );

                $carry[] = new FacetField($attributeCode, ...$facetFieldValues);

                return $carry;
            },
            []
        );
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
     * @param FacetFieldCollection $facetFieldCollection
     * @param SearchDocument[] $searchDocuments
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @return SearchEngineResponse
     */
    private function createSearchEngineResponse(
        FacetFieldCollection $facetFieldCollection,
        array $searchDocuments,
        $rowsPerPage,
        $pageNumber
    ) {
        $totalNumberOfResults = count($searchDocuments);
        $currentPageDocuments = array_slice($searchDocuments, $pageNumber * $rowsPerPage, $rowsPerPage);
        $documentCollection = new SearchDocumentCollection(...array_values($currentPageDocuments));

        return new SearchEngineResponse($documentCollection, $facetFieldCollection, $totalNumberOfResults);
    }

    /**
     * @param array[] $attributeValues
     * @param FacetFilterRequestField $facetFilterRequestField
     * @return FacetFieldValue[]
     */
    private function getFacetFieldValuesFromAttributeValues(
        array $attributeValues,
        FacetFilterRequestField $facetFilterRequestField
    ) {
        if ($facetFilterRequestField->isRanged()) {
            return $this->createRangedFacetFieldFromAttributeValues($attributeValues, $facetFilterRequestField);
        }

        return $this->createDefaultFacetFieldFromAttributeValues($attributeValues);
    }

    /**
     * @param array[] $attributeValues
     * @return FacetFieldValue[]
     */
    private function createDefaultFacetFieldFromAttributeValues(array $attributeValues)
    {
        return array_map(function ($valueCounts) {
            return FacetFieldValue::create((string)$valueCounts['value'], $valueCounts['count']);
        }, $attributeValues);
    }

    /**
     * @param array[] $attributeValues
     * @param FacetFilterRequestRangedField $facetFilterRequestRangedField
     * @return FacetFieldValue[]
     */
    private function createRangedFacetFieldFromAttributeValues(
        array $attributeValues,
        FacetFilterRequestRangedField $facetFilterRequestRangedField
    ) {
        $ranges = $facetFilterRequestRangedField->getRanges();
        return array_reduce($ranges, function ($carry, FacetFilterRange $range) use ($attributeValues) {
            $rangeCount = $this->sumAttributeValuesCountsInRange($range, $attributeValues);
            if ($rangeCount > 0) {
                $rangeCode = $range->from() . SearchEngine::RANGE_DELIMITER . $range->to();
                $carry[] = FacetFieldValue::create($rangeCode, $rangeCount);
            }

            return $carry;
        }, []);
    }

    /**
     * @param FacetFilterRange $range
     * @param array[] $attributeValues
     * @return int
     */
    private function sumAttributeValuesCountsInRange(FacetFilterRange $range, $attributeValues)
    {
        return array_reduce($attributeValues, function ($carry, array $valueCounts) use ($range) {
            if ((null === $range->from() || $valueCounts['value'] >= $range->from()) &&
                (null === $range->to() || $valueCounts['value'] <= $range->to())
            ) {
                $carry += $valueCounts['count'];
            }

            return $carry;
        }, 0);
    }

    /**
     * @param SearchDocument[] $documents
     * @param SearchCriteria $criteria
     * @param Context $context
     * @return SearchDocument[]
     */
    private function filterDocumentsMatchingCriteria(array $documents, SearchCriteria $criteria, Context $context)
    {
        return array_filter($documents, function (SearchDocument $document) use ($criteria, $context) {
            return $criteria->matches($document) && $context->isSubsetOf($document->getContext());
        });
    }

    /**
     * @param SortOrderConfig $sortOrderConfig
     * @param SearchDocument[] $unsortedDocuments
     * @return SearchDocument[]
     */
    private function getSortedDocuments(SortOrderConfig $sortOrderConfig, SearchDocument ...$unsortedDocuments)
    {
        $result = $unsortedDocuments;
        $field = $sortOrderConfig->getAttributeCode();
        $direction = $sortOrderConfig->getSelectedDirection();

        usort($result, function (SearchDocument $documentA, SearchDocument $documentB) use ($field, $direction) {
            $fieldA = $this->getSortableSearchDocumentFieldValue($documentA, $field);
            $fieldB = $this->getSortableSearchDocumentFieldValue($documentB, $field);

            if ($fieldA === $fieldB) {
                return 0;
            }

            if (SortOrderDirection::ASC === $direction && $fieldA < $fieldB ||
                SortOrderDirection::DESC === $direction && $fieldA > $fieldB
            ) {
                return -1;
            }

            return 1;
        });

        return $result;
    }

    /**
     * @param SearchDocument $document
     * @param AttributeCode $fieldName
     * @return mixed
     */
    private function getSortableSearchDocumentFieldValue(SearchDocument $document, AttributeCode $fieldName)
    {
        foreach ($document->getFieldsCollection()->getFields() as $field) {
            if ($field->getKey() !== (string) $fieldName) {
                continue;
            }

            $values = $field->getValues();

            if (count($values) === 1) {
                return $this->getFormattedSearchDocumentValue($values[0]);
            }

            return null;
        }

        return null;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function getFormattedSearchDocumentValue($value)
    {
        if (is_string($value)) {
            return strtolower($value);
        }

        return $value;
    }
}
