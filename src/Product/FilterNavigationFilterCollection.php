<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\LocaleContextDecorator;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
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

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        DataPoolReader $dataPoolReader,
        TranslatorRegistry $translatorRegistry,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->translatorRegistry = $translatorRegistry;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
     * @param SearchDocumentCollection $searchDocumentCollection
     * @param SearchCriteria $originalCriteria
     * @param array[] $selectedFilters
     * @param Context $context
     */
    public function initialize(
        SearchDocumentCollection $searchDocumentCollection,
        SearchCriteria $originalCriteria,
        array $selectedFilters,
        Context $context
    ) {
        $this->selectedFilters = $selectedFilters;
        $attributeCodes = array_keys($this->selectedFilters);

        $collectionOptionValues = $this->getCollectionOptionValuesForAttributes(
            $searchDocumentCollection,
            $attributeCodes
        );
        $selectedOptionValuesWithSiblings = $this->getSelectedOptionValuesWithSiblings($originalCriteria, $context);
        $filters = array_merge($collectionOptionValues, $selectedOptionValuesWithSiblings);

        $translator = $this->getTranslatorForContext($context);

        $this->initializeFiltersFromArray($filters, $translator);
    }

    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @param string[] $attributeCodes
     * @return array[]
     */
    private function getCollectionOptionValuesForAttributes(
        SearchDocumentCollection $searchDocumentCollection,
        array $attributeCodes
    ) {
        $filters = [];

        /** @var SearchDocument $searchDocument */
        foreach ($searchDocumentCollection as $searchDocument) {
            /** @var SearchDocumentField $searchDocumentField */
            foreach ($searchDocument->getFieldsCollection() as $searchDocumentField) {
                $filterCode = $searchDocumentField->getKey();
                if (!in_array($filterCode, $attributeCodes)) {
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
     * @return array
     */
    private function getSelectedOptionValuesWithSiblings(SearchCriteria $originalCriteria, Context $context)
    {
        $selectedAttributes = array_keys(array_filter($this->selectedFilters));

        return array_reduce($selectedAttributes, function ($carry, $attributeCode) use ($originalCriteria, $context) {
            $carry[$attributeCode] = $this->getFilterOptionsWithApplicableSiblings(
                $originalCriteria,
                $context,
                $attributeCode
            );
            return $carry;
        }, []);
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
        $customCriteria = $this->addFiltersExceptGivenOneToSearchCriteria($originalCriteria, $filterCode);
        $searchDocumentCollection = $this->dataPoolReader->getSearchDocumentsMatchingCriteria(
            $customCriteria,
            $context
        );
        $filter = $this->getCollectionOptionValuesForAttributes($searchDocumentCollection, [$filterCode]);

        return $filter[$filterCode];
    }

    /**
     * @param SearchCriteria $originalCriteria
     * @param string $filterCodeToExclude
     * @return SearchCriteria
     */
    private function addFiltersExceptGivenOneToSearchCriteria(SearchCriteria $originalCriteria, $filterCodeToExclude)
    {
        $filtersCriteriaArray = [];

        foreach ($this->selectedFilters as $filterCode => $filterOptionValues) {
            if ($filterCode === $filterCodeToExclude || empty($filterOptionValues)) {
                continue;
            }

            $optionValuesCriteriaArray = array_map(function ($filterOptionValue) use ($filterCode) {
                return $this->searchCriteriaBuilder->create($filterCode, $filterOptionValue);
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
            $filterNavigationFilterOptions = [];
            foreach ($filterOptions as $optionValue => $optionCount) {
                $filterNavigationFilterOptions[] = FilterNavigationFilterOption::create($optionValue, $optionCount);
            }
            $filterOptionCollection = new FilterNavigationFilterOptionCollection(...$filterNavigationFilterOptions);
            $this->filters[] = FilterNavigationFilter::create($filterCode, $filterOptionCollection, $translator);
        }
    }

    /**
     * @param Context $context
     * @return Translator
     */
    private function getTranslatorForContext(Context $context)
    {
        $locale = $context->getValue(LocaleContextDecorator::CODE);
        return $this->translatorRegistry->getTranslatorForLocale($locale);
    }
}
