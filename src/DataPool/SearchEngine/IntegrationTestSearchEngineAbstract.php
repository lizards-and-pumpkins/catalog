<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\Exception\NoFacetFieldTransformationRegisteredException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
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
     * @return FacetFieldTransformationRegistry
     */
    abstract protected function getFacetFieldTransformationRegistry();

    /**
     * {@inheritdoc}
     */
    final public function query(
        SearchCriteria $originalCriteria,
        array $filterSelection,
        Context $context,
        FacetFiltersToIncludeInResult $facetFilterRequest,
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
        if (count($filters) === 0) {
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
        return @array_map(function ($filterCode) use ($filters) {
            $optionValuesCriteriaArray = $this->createOptionValuesCriteriaArray($filterCode, $filters[$filterCode]);
            return CompositeSearchCriterion::createOr(...$optionValuesCriteriaArray);
        }, array_keys($filters));
    }

    /**
     * @param SearchCriteria $originalCriteria
     * @param Context $context
     * @param FacetFiltersToIncludeInResult $facetFilterRequest
     * @param array[] $selectedFilters
     * @param SearchDocument[] $matchingDocuments
     * @param SearchDocument[] $allDocuments
     * @return FacetFieldCollection
     */
    private function createFacetFieldCollection(
        SearchCriteria $originalCriteria,
        Context $context,
        FacetFiltersToIncludeInResult $facetFilterRequest,
        array $selectedFilters,
        array $matchingDocuments,
        array $allDocuments
    ) {
        if (count($matchingDocuments) === 0) {
            return new FacetFieldCollection();
        }
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
     * @param FacetFiltersToIncludeInResult $facetFilterRequest
     * @return FacetField[]
     */
    private function getSelectedFiltersFacetValuesWithSiblings(
        SearchCriteria $originalCriteria,
        Context $context,
        array $selectedFilters,
        array $allDocuments,
        FacetFiltersToIncludeInResult $facetFilterRequest
    ) {
        if (count($facetFilterRequest->getFields()) === 0) {
            return [];
        }
        
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
        return @array_map(function ($filterOptionValue) use ($filterCode) {
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
     * @param string[] $selectedFacetFieldCodes
     * @param FacetFiltersToIncludeInResult $facetFilterRequest
     * @param SearchDocument[] $searchDocuments
     * @return FacetField[]
     */
    private function createFacetFieldsFromSearchDocuments(
        array $selectedFacetFieldCodes,
        FacetFiltersToIncludeInResult $facetFilterRequest,
        SearchDocument ...$searchDocuments
    ) {
        $attributeCounts = $this->createAttributeValueCountArrayFromSearchDocuments(
            $selectedFacetFieldCodes,
            ...$searchDocuments
        );

        return @array_reduce(
            $facetFilterRequest->getFields(),
            function (array $carry, FacetFilterRequestField $field) use ($attributeCounts, $selectedFacetFieldCodes) {
                $attributeCode = $field->getAttributeCode();
                $attributeCodeString = (string) $attributeCode;

                $isSelectedFilter = in_array($attributeCodeString, $selectedFacetFieldCodes);
                $hasMatchingDocuments = isset($attributeCounts[$attributeCodeString]);
                if (!$isSelectedFilter || !$hasMatchingDocuments) {
                    return $carry;
                }

                $facetFieldValues = $this->getFacetFieldValuesFromAttributeValues(
                    $attributeCounts[$attributeCodeString],
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
        $productIds = array_map(function (SearchDocument $document) {
            return $document->getProductId();
        }, $currentPageDocuments);

        return new SearchEngineResponse($facetFieldCollection, $totalNumberOfResults, ...$productIds);
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

        return $this->createSimpleFacetFieldFromAttributeValues($attributeValues);
    }

    /**
     * @param array[] $attributeValues
     * @return FacetFieldValue[]
     */
    private function createSimpleFacetFieldFromAttributeValues(array $attributeValues)
    {
        $attributeValues = $this->sortAttributeValuesAlphabetically($attributeValues);

        return array_map(function ($valueCounts) {
            return FacetFieldValue::create((string) $valueCounts['value'], $valueCounts['count']);
        }, $attributeValues);
    }

    /**
     * @param array[] $attributeValues
     * @return mixed
     */
    private function sortAttributeValuesAlphabetically(array $attributeValues)
    {
        usort($attributeValues, function ($a, $b) {
            return strcmp($a['value'], $b['value']);
        });

        return $attributeValues;
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
        $attributeCode = (string) $facetFilterRequestRangedField->getAttributeCode();

        $ranges = $facetFilterRequestRangedField->getRanges();
        return @array_reduce(
            $ranges,
            function ($carry, FacetFilterRange $range) use ($attributeValues, $attributeCode) {
                $rangeCount = $this->sumAttributeValuesCountsInRange($range, $attributeValues);

                if ($rangeCount > 0) {
                    $rangeCode = $this->getRangedFilterCode($range, $attributeCode);
                    $carry[] = FacetFieldValue::create($rangeCode, $rangeCount);
                }

                return $carry;
            },
            []
        );
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
     * @param FacetFilterRange $range
     * @param string $attributeCode
     * @return string
     */
    private function getRangedFilterCode(FacetFilterRange $range, $attributeCode)
    {
        $transformationRegistry = $this->getFacetFieldTransformationRegistry();

        if (!$transformationRegistry->hasTransformationForCode($attributeCode)) {
            throw new NoFacetFieldTransformationRegisteredException(
                sprintf('No facet field transformation is registered for "%s" attribute.', $attributeCode)
            );
        }

        $transformation = $transformationRegistry->getTransformationByCode($attributeCode);

        return $transformation->encode($range);
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
            return $criteria->matches($document) && $context->contains($document->getContext());
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
