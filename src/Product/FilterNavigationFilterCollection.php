<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;

class FilterNavigationFilterCollection implements \Countable
{
    /**
     * @var FilterNavigationFilter[]
     */
    private $filters = [];

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
        return count($this->filters);
    }

    /**
     * @return FilterNavigationFilter[]
     */
    public function getFilters()
    {
        return $this->filters;
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
        $allowedFilterCodes = array_keys($selectedFilters);
        $defaultFilters = $this->getFiltersAppliedToCollection($originalSearchDocumentCollection, $allowedFilterCodes);

        $filters = [];
        foreach ($selectedFilters as $selectedFilterCode => $selectedFilterValues) {
            if (empty($selectedFilterValues)) {
                if (isset($defaultFilters[$selectedFilterCode])) {
                    $filters[$selectedFilterCode] = $defaultFilters[$selectedFilterCode];
                }
                continue;
            }

            $customCriteria = $this->addFiltersExceptGivenOneToSearchCriteria(
                $originalCriteria,
                $selectedFilters,
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
                if (in_array($filterValueString, $selectedFilters[$filterCode])) {
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

        foreach ($searchDocumentCollection->getDocuments() as $searchDocument) {
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
     * @param array $selectedFilters
     * @param string $filterCodeToExclude
     * @return SearchCriteria
     */
    private function addFiltersExceptGivenOneToSearchCriteria(
        SearchCriteria $originalCriteria,
        array $selectedFilters,
        $filterCodeToExclude
    ) {
        $filtersCriteria = SearchCriteria::createAnd();

        foreach ($selectedFilters as $filterCode => $filterValues) {
            if ($filterCode === $filterCodeToExclude || empty($filterValues)) {
                continue;
            }

            $filterCriteria = SearchCriteria::createOr();
            foreach ($filterValues as $filterValue) {
                $filterCriteria->addCriterion(SearchCriterion::create($filterCode, $filterValue, '='));
            }
            $filtersCriteria->addCriteria($filterCriteria);
        }

        if (empty($filtersCriteria->getCriteria())) {
            return $originalCriteria;
        }

        $filtersCriteria->addCriteria($originalCriteria);

        return $filtersCriteria;
    }
}
