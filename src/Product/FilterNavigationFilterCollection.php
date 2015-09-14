<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use Brera\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use Brera\Product\Exception\FilterCollectionInNotInitializedException;

class FilterNavigationFilterCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var FilterNavigationFilter[]
     */
    private $filters;

    /**
     * @var array[]
     */
    private $selectedFilters;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    public function __construct(DataPoolReader $dataPoolReader)
    {
        $this->dataPoolReader = $dataPoolReader;
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->validateFiltersCollectionIsInitialized();
        return count($this->filters);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $this->validateFiltersCollectionIsInitialized();
        return new \ArrayIterator($this->filters);
    }

    /**
     * @return FilterNavigationFilter[]
     */
    public function getFilters()
    {
        $this->validateFiltersCollectionIsInitialized();
        return $this->filters;
    }

    /**
     * @return array[]
     */
    public function getSelectedFilters()
    {
        $this->validateFiltersCollectionIsInitialized();
        return $this->selectedFilters;
    }

    /**
     * @param SearchDocumentCollection $originalSearchDocumentCollection
     * @param SearchCriteria $originalCriteria
     * @param array[] $selectedFilters
     * @param Context $context
     */
    public function initialize(
        SearchDocumentCollection $originalSearchDocumentCollection,
        SearchCriteria $originalCriteria,
        array $selectedFilters,
        Context $context
    ) {
        $this->selectedFilters = $selectedFilters;

        $allowedFilterCodes = array_keys($this->selectedFilters);
        $defaultFilters = $this->getFiltersAppliedToCollection($originalSearchDocumentCollection, $allowedFilterCodes);

        $filters = [];
        foreach ($this->selectedFilters as $selectedFilterCode => $selectedFilterValues) {
            if (empty($selectedFilterValues)) {
                if (isset($defaultFilters[$selectedFilterCode])) {
                    $filters[$selectedFilterCode] = $defaultFilters[$selectedFilterCode];
                }
                continue;
            }

            $filters[$selectedFilterCode] = $this->getFilterOptionsWithApplicableSiblings(
                $originalCriteria,
                $context,
                $selectedFilterCode
            );
        }

        $this->initializeFiltersFromArray($filters);
    }

    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @param string[] $filtersCodesToFetch
     * @return array[]
     */
    private function getFiltersAppliedToCollection(
        SearchDocumentCollection $searchDocumentCollection,
        array $filtersCodesToFetch
    ) {
        $filters = [];

        /** @var SearchDocument $searchDocument */
        foreach ($searchDocumentCollection as $searchDocument) {
            /** @var SearchDocumentField $searchDocumentField */
            foreach ($searchDocument->getFieldsCollection() as $searchDocumentField) {
                $filterCode = $searchDocumentField->getKey();
                if (!in_array($filterCode, $filtersCodesToFetch)) {
                    continue;
                }
                $filterValue = $searchDocumentField->getValue();
                if (!isset($filters[$filterCode])) {
                    $filters[$filterCode] = [];
                }
                if (!isset($filters[$filterCode][$filterValue])) {
                    $filters[$filterCode][$filterValue] = 0;
                }
                $filters[$filterCode][$filterValue] ++;
            }
        }

        return $filters;
    }

    /**
     * @param SearchCriteria $originalCriteria
     * @param Context $context
     * @param string $filterCode
     * @return array[]
     */
    private function getFilterOptionsWithApplicableSiblings(
        SearchCriteria $originalCriteria,
        Context $context,
        $filterCode
    ) {
        $customCriteria = $this->addFiltersExceptGivenOneToSearchCriteria(
            $originalCriteria,
            $this->selectedFilters,
            $filterCode
        );
        $searchDocumentCollection = $this->dataPoolReader->getSearchDocumentsMatchingCriteria(
            $customCriteria,
            $context
        );
        $filter = $this->getFiltersAppliedToCollection($searchDocumentCollection, [$filterCode]);

        return $filter[$filterCode];
    }

    /**
     * @param SearchCriteria $originalCriteria
     * @param array[] $selectedFilters
     * @param string $filterCodeToExclude
     * @return SearchCriteria
     */
    private function addFiltersExceptGivenOneToSearchCriteria(
        SearchCriteria $originalCriteria,
        array $selectedFilters,
        $filterCodeToExclude
    ) {
        $filtersCriteriaArray = [];

        foreach ($selectedFilters as $filterCode => $filterOptionValues) {
            if ($filterCode === $filterCodeToExclude || empty($filterOptionValues)) {
                continue;
            }

            $optionValuesCriteriaArray = array_map(function ($filterOptionValue) use ($filterCode) {
                return SearchCriterionEqual::create($filterCode, $filterOptionValue);
            }, $filterOptionValues);

            $filterCriteria = CompositeSearchCriterion::createOr(...$optionValuesCriteriaArray);
            $filtersCriteriaArray[] = $filterCriteria;
        }

        if (empty($filtersCriteriaArray)) {
            return $originalCriteria;
        }

        $filtersCriteriaArray[] = $originalCriteria;
        return CompositeSearchCriterion::createAnd(...$filtersCriteriaArray);
    }

    private function validateFiltersCollectionIsInitialized()
    {
        if (null === $this->filters) {
            throw new FilterCollectionInNotInitializedException('Filters collection is not initialized.');
        }
    }

    /**
     * @param array[] $filters
     */
    private function initializeFiltersFromArray(array $filters)
    {
        $this->filters = [];

        foreach ($filters as $filterCode => $filterOptions) {
            $filterNavigationFilterOptionCollection = new FilterNavigationFilterOptionCollection;
            foreach ($filterOptions as $optionValue => $optionCount) {
                if (in_array($optionValue, $this->selectedFilters[$filterCode])) {
                    $filterOption = FilterNavigationFilterOption::createSelected(
                        $filterCode,
                        $optionValue,
                        $optionCount
                    );
                } else {
                    $filterOption = FilterNavigationFilterOption::create($filterCode, $optionValue, $optionCount);
                }
                $filterNavigationFilterOptionCollection->add($filterOption);
            }
            $this->filters[] = FilterNavigationFilter::create($filterCode, $filterNavigationFilterOptionCollection);
        }
    }
}
