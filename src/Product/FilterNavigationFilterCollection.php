<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\SearchEngine\CompositeSearchCriterion;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;

class FilterNavigationFilterCollection implements \Countable
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
        $this->filters = [];
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

            $customCriteria = $this->addFiltersExceptGivenOneToSearchCriteria(
                $originalCriteria,
                $this->selectedFilters,
                $selectedFilterCode
            );
            $searchDocumentCollection = $this->dataPoolReader->getSearchDocumentsMatchingCriteria(
                $customCriteria,
                $context
            );
            $filter = $this->getFiltersAppliedToCollection($searchDocumentCollection, [$selectedFilterCode]);
            $filters[$selectedFilterCode] = $filter[$selectedFilterCode];
        }

        foreach ($filters as $filterCode => $filterValues) {
            $filterNavigationFilterValueCollection = new FilterNavigationFilterValueCollection;
            foreach ($filterValues as $filterValueString => $filterValueCount) {
                if (in_array($filterValueString, $this->selectedFilters[$filterCode])) {
                    $filterValue = FilterNavigationFilterValue::createSelected($filterValueString, $filterValueCount);
                } else {
                    $filterValue = FilterNavigationFilterValue::create($filterValueString, $filterValueCount);
                }
                $filterNavigationFilterValueCollection->add($filterValue);
            }
            $this->filters[] = FilterNavigationFilter::create($filterCode, $filterNavigationFilterValueCollection);
        }
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
            foreach ($searchDocument->getFieldsCollection()->getFields() as $searchDocumentField) {
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
     * @param array[] $selectedFilters
     * @param string $filterCodeToExclude
     * @return SearchCriteria
     */
    private function addFiltersExceptGivenOneToSearchCriteria(
        SearchCriteria $originalCriteria,
        array $selectedFilters,
        $filterCodeToExclude
    ) {
        $filtersCriteria = CompositeSearchCriterion::createAnd();
        $somethingIsAddedToCriteria = false;

        foreach ($selectedFilters as $filterCode => $filterValues) {
            if ($filterCode === $filterCodeToExclude || empty($filterValues)) {
                continue;
            }

            $filterCriteria = CompositeSearchCriterion::createOr();
            foreach ($filterValues as $filterValue) {
                $filterCriteria->addCriteria(SearchCriterion::create($filterCode, $filterValue, '='));
            }
            $filtersCriteria->addCriteria($filterCriteria);
            $somethingIsAddedToCriteria = true;
        }

        if (false === $somethingIsAddedToCriteria) {
            return $originalCriteria;
        }

        $filtersCriteria->addCriteria($originalCriteria);

        return $filtersCriteria;
    }

    private function validateFiltersCollectionIsInitialized()
    {
        if (null === $this->filters) {
            throw new FilterCollectionInNotInitializedException('Filters collection is not initialized.');
        }
    }
}
