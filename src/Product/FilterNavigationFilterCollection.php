<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\LocaleContextDecorator;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\Product\Exception\FilterCollectionInNotInitializedException;
use LizardsAndPumpkins\Renderer\Translation\Translator;
use LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry;

class FilterNavigationFilterCollection implements \Countable, \IteratorAggregate, \JsonSerializable
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

    /**
     * @var TranslatorRegistry
     */
    private $translatorRegistry;

    public function __construct(DataPoolReader $dataPoolReader, TranslatorRegistry $translatorRegistry)
    {
        $this->dataPoolReader = $dataPoolReader;
        $this->translatorRegistry = $translatorRegistry;
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
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        $this->validateFiltersCollectionIsInitialized();
        return $this->filters;
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

        $locale = $context->getValue(LocaleContextDecorator::CODE);
        $translator = $this->translatorRegistry->getTranslatorForLocale($locale);

        $this->initializeFiltersFromArray($filters, $translator);
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
                if (!isset($filters[$filterCode])) {
                    $filters[$filterCode] = [];
                }
                foreach ($searchDocumentField->getValues() as $filterValue) {
                    if (!isset($filters[$filterCode][$filterValue])) {
                        $filters[$filterCode][$filterValue] = 0;
                    }
                    $filters[$filterCode][$filterValue]++;
                }
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
     * @param Translator $translator
     */
    private function initializeFiltersFromArray(array $filters, Translator $translator)
    {
        $this->filters = [];

        foreach ($filters as $filterCode => $filterOptions) {
            $filterNavigationFilterOptionCollection = new FilterNavigationFilterOptionCollection;
            foreach ($filterOptions as $optionValue => $optionCount) {
                if (in_array($optionValue, $this->selectedFilters[$filterCode])) {
                    $filterOption = FilterNavigationFilterOption::createSelected($optionValue, $optionCount);
                } else {
                    $filterOption = FilterNavigationFilterOption::create($optionValue, $optionCount);
                }
                $filterNavigationFilterOptionCollection->add($filterOption);
            }
            $this->filters[] = FilterNavigationFilter::create(
                $filterCode,
                $filterNavigationFilterOptionCollection,
                $translator
            );
        }
    }
}
