<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetField;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldValue;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestField;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestRangedField;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation\IntegrationTestSearchEngineOperation;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\Exception\NoFacetFieldTransformationRegisteredException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\Exception\InvalidCriterionConditionException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\Exception\UnsupportedSearchCriteriaOperationException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterOrEqualThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessOrEqualThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\Util\Storage\Clearable;

abstract class IntegrationTestSearchEngineAbstract implements SearchEngine, Clearable
{
    /**
     * @return SearchDocument[]
     */
    abstract protected function getSearchDocuments() : array;

    abstract protected function getFacetFieldTransformationRegistry() : FacetFieldTransformationRegistry;

    /**
     * @return string[]
     */
    abstract protected function getSearchableFields() : array;

    final public function query(SearchCriteria $originalCriteria, QueryOptions $queryOptions) : SearchEngineResponse
    {
        $selectedFilters = array_filter($queryOptions->getFilterSelection());
        $criteria = $this->applyFiltersToSelectionCriteria($originalCriteria, $selectedFilters);

        $allDocuments = array_values($this->getSearchDocuments());
        $context = $queryOptions->getContext();
        $matchingDocuments = $this->filterDocumentsMatchingCriteria($criteria, $context, ...$allDocuments);

        $facetFieldCollection = $this->createFacetFieldCollection(
            $originalCriteria,
            $context,
            $queryOptions->getFacetFiltersToIncludeInResult(),
            $selectedFilters,
            $matchingDocuments,
            $allDocuments
        );

        $sortBy = $queryOptions->getSortBy();
        $sortedDocuments = $this->getSortedDocuments($sortBy, ...array_values($matchingDocuments));

        $rowsPerPage = $queryOptions->getRowsPerPage();
        $pageNumber = $queryOptions->getPageNumber();

        return $this->createSearchEngineResponse($facetFieldCollection, $sortedDocuments, $rowsPerPage, $pageNumber);
    }

    /**
     * @param SearchCriteria $originalCriteria
     * @param array[] $filters
     * @return SearchCriteria
     */
    private function applyFiltersToSelectionCriteria(SearchCriteria $originalCriteria, array $filters) : SearchCriteria
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
    private function createSearchEngineCriteriaForFilters(array $filters) : array
    {
        return array_map(function ($filterCode) use ($filters) {
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
    ) : FacetFieldCollection {
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
    ) : array {
        if (count($facetFilterRequest->getFields()) === 0) {
            return [];
        }
        
        $facetFieldsForSelectedFilters = [];
        foreach (array_keys($selectedFilters) as $filterCode) {
            $selectedFiltersExceptCurrentOne = array_diff_key($selectedFilters, [$filterCode => []]);

            $criteria = $this->applyFiltersToSelectionCriteria($originalCriteria, $selectedFiltersExceptCurrentOne);
            $matchingDocuments = $this->filterDocumentsMatchingCriteria($criteria, $context, ...$allDocuments);

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
    private function createOptionValuesCriteriaArray(string $filterCode, array $filterOptionValues) : array
    {
        return array_map(function ($filterOptionValue) use ($filterCode) {
            return $this->fromFieldNameAndValue($filterCode, $filterOptionValue);
        }, $filterOptionValues);
    }

    private function fromFieldNameAndValue(string $fieldName, string $fieldValue) : SearchCriteria
    {
        $facetFieldTransformationRegistry = $this->getFacetFieldTransformationRegistry();

        if ($facetFieldTransformationRegistry->hasTransformationForCode($fieldName)) {
            $transformation = $facetFieldTransformationRegistry->getTransformationByCode($fieldName);
            $range = $transformation->decode($fieldValue);

            $criterionFrom = new SearchCriterionGreaterOrEqualThan($fieldName, $range->from());
            $criterionTo = new SearchCriterionLessOrEqualThan($fieldName, $range->to());

            return CompositeSearchCriterion::createAnd($criterionFrom, $criterionTo);
        }

        return new SearchCriterionEqual($fieldName, $fieldValue);
    }

    final protected function getSearchDocumentIdentifier(SearchDocument $searchDocument) : string
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
    ) : array {
        $attributeCounts = $this->createAttributeValueCountArrayFromSearchDocuments(
            $selectedFacetFieldCodes,
            ...$searchDocuments
        );

        return array_reduce(
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
    ) : array {
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
    private function addDocumentToCount(
        array $attributeValuesCount,
        SearchDocument $document,
        array $facetFieldCodes
    ) : array {
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
        string $attributeCode,
        SearchDocumentField $documentField
    ) : array {
        return array_reduce($documentField->getValues(), function ($carry, $value) use ($attributeCode) {
            return $this->addValueToCount($carry, $attributeCode, $value);
        }, $attributeValuesCounts);
    }

    /**
     * @param array[] $attributeValuesCounts
     * @param string $attributeCode
     * @param mixed $newValue
     * @return array[]
     */
    private function addValueToCount(array $attributeValuesCounts, string $attributeCode, $newValue) : array
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
        int $rowsPerPage,
        int $pageNumber
    ) : SearchEngineResponse {
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
    ) : array {
        if ($facetFilterRequestField->isRanged()) {
            return $this->createRangedFacetFieldFromAttributeValues($attributeValues, $facetFilterRequestField);
        }

        return $this->createSimpleFacetFieldFromAttributeValues($attributeValues);
    }

    /**
     * @param array[] $attributeValues
     * @return FacetFieldValue[]
     */
    private function createSimpleFacetFieldFromAttributeValues(array $attributeValues) : array
    {
        $attributeValues = $this->sortAttributeValuesAlphabetically($attributeValues);

        return array_map(function ($valueCounts) {
            return new FacetFieldValue((string) $valueCounts['value'], $valueCounts['count']);
        }, $attributeValues);
    }

    /**
     * @param array[] $attributeValues
     * @return mixed
     */
    private function sortAttributeValuesAlphabetically(array $attributeValues)
    {
        usort($attributeValues, function ($a, $b) {
            return strcmp((string) $a['value'], (string) $b['value']);
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
    ) : array {
        $attributeCode = (string) $facetFilterRequestRangedField->getAttributeCode();

        $ranges = $facetFilterRequestRangedField->getRanges();
        return array_reduce(
            $ranges,
            function ($carry, FacetFilterRange $range) use ($attributeValues, $attributeCode) {
                $rangeCount = $this->sumAttributeValuesCountsInRange($range, $attributeValues);

                if ($rangeCount > 0) {
                    $rangeCode = $this->getRangedFilterCode($range, $attributeCode);
                    $carry[] = new FacetFieldValue($rangeCode, $rangeCount);
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
    private function sumAttributeValuesCountsInRange(FacetFilterRange $range, array $attributeValues) : int
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

    private function getRangedFilterCode(FacetFilterRange $range, string $attributeCode) : string
    {
        $facetFieldTransformationRegistry = $this->getFacetFieldTransformationRegistry();

        if (! $facetFieldTransformationRegistry->hasTransformationForCode($attributeCode)) {
            throw new NoFacetFieldTransformationRegisteredException(
                sprintf('No facet field transformation is registered for "%s" attribute.', $attributeCode)
            );
        }

        $transformation = $facetFieldTransformationRegistry->getTransformationByCode($attributeCode);

        return $transformation->encode($range);
    }

    /**
     * @param SearchCriteria $criteria
     * @param Context $context
     * @param SearchDocument[] ...$documents
     * @return SearchDocument[]
     */
    private function filterDocumentsMatchingCriteria(
        SearchCriteria $criteria,
        Context $context,
        SearchDocument ...$documents
    ) : array {
        return array_filter($documents, function (SearchDocument $document) use ($criteria, $context) {
            return $this->isSearchDocumentMatchesCriteria($document, $criteria->toArray()) &&
                   $context->contains($document->getContext());
        });
    }

    /**
     * @param SearchDocument $document
     * @param mixed[] $criteriaArray
     * @return bool
     */
    private function isSearchDocumentMatchesCriteria(SearchDocument $document, array $criteriaArray) : bool
    {
        if (isset($criteriaArray['condition'])) {
            $subOperationResults = array_map(function (array $subCriteriaArray) use ($document) {
                return $this->isSearchDocumentMatchesCriteria($document, $subCriteriaArray);
            }, $criteriaArray['criteria']);

            return $this->computeCompositeCriterion($criteriaArray['condition'], ...$subOperationResults);
        }

        $operation = $this->createOperation($criteriaArray['operation'], $criteriaArray);

        return $operation->matches($document);
    }

    private function computeCompositeCriterion(string $condition, bool ...$subOperationResults) : bool
    {
        if (strcasecmp($condition, CompositeSearchCriterion::AND_CONDITION) === 0) {
            return array_reduce($subOperationResults, function ($carry, $operationResult) {
                return $carry && $operationResult;
            }, true);
        }

        if (strcasecmp($condition, CompositeSearchCriterion::OR_CONDITION) === 0) {
            return array_reduce($subOperationResults, function ($carry, $operationResult) {
                return $carry || $operationResult;
            }, false);
        }

        throw new InvalidCriterionConditionException(sprintf('Unknown search criteria condition "%s".', $condition));
    }

    /**
     * @param string $operation
     * @param string[] $data
     * @return IntegrationTestSearchEngineOperation
     */
    private function createOperation(string $operation, array $data) : IntegrationTestSearchEngineOperation
    {
        $className = __NAMESPACE__ . '\\Operation\\IntegrationTestSearchEngineOperation' . $operation;

        if (! class_exists($className)) {
            throw new UnsupportedSearchCriteriaOperationException(
                sprintf('Unsupported integration test search engine criterion operation "%s".', $operation)
            );
        }

        return new $className($data);
    }

    /**
     * @param SortBy $sortBy
     * @param SearchDocument[] ...$unsortedDocuments
     * @return SearchDocument[]
     */
    private function getSortedDocuments(SortBy $sortBy, SearchDocument ...$unsortedDocuments) : array
    {
        $result = $unsortedDocuments;
        $field = $sortBy->getAttributeCode();
        $direction = (string) $sortBy->getSelectedDirection();

        usort($result, function (SearchDocument $documentA, SearchDocument $documentB) use ($field, $direction) {
            $fieldA = $this->getSortableSearchDocumentFieldValue($documentA, $field);
            $fieldB = $this->getSortableSearchDocumentFieldValue($documentB, $field);

            if ($fieldA === $fieldB) {
                return 0;
            }

            if (SortDirection::ASC === $direction && $fieldA < $fieldB ||
                SortDirection::DESC === $direction && $fieldA > $fieldB
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
