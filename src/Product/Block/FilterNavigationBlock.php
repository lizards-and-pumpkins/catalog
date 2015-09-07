<?php

namespace Brera\Product\Block;

use Brera\Product\FilterNavigationFilterCollection;
use Brera\Product\FilterNavigationFilterOption;
use Brera\Renderer\Block;
use Brera\Renderer\InvalidDataObjectException;

class FilterNavigationBlock extends Block
{
    const VALUES_SEPARATOR = ',';

    /**
     * @var array[]
     */
    private $lazyLoadedSelectedFilters;

    /**
     * @return FilterNavigationFilterCollection
     */
    public function getFilterCollection()
    {
        $this->validateDataObject();
        return $this->getDataObject();
    }

    private function validateDataObject()
    {
        $dataObject = $this->getDataObject();
        if (!($dataObject instanceof FilterNavigationFilterCollection)) {
            throw new InvalidDataObjectException(
                sprintf(
                    'Data object must be instance of %s, got "%s".',
                    FilterNavigationFilterCollection::class,
                    $this->getVariableType($dataObject)
                )
            );
        }
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private function getVariableType($variable)
    {
        return 'object' !== gettype($variable) ?
            gettype($variable) :
            get_class($variable);
    }

    /**
     * @param FilterNavigationFilterOption $filterOption
     * @return string
     */
    public function getQueryStringForFilterSelection(FilterNavigationFilterOption $filterOption)
    {
        $queryArgumentsForSelection = $this->getQueryArgumentsForSelection($filterOption);
        return $this->buildHttpQueryFromArray($queryArgumentsForSelection);
    }

    /**
     * @param FilterNavigationFilterOption $filterOption
     * @return \string[]
     */
    private function getQueryArgumentsForSelection(FilterNavigationFilterOption $filterOption)
    {
        $filterCodes = array_keys($this->getSelectedFilters());
        return array_reduce($filterCodes, function (array $selection, $codeToCheck) use ($filterOption) {
            $selectedValues = $this->getUpdatedQueryParameterForFilterOption($codeToCheck, $filterOption);
            $selection[$codeToCheck] = implode(self::VALUES_SEPARATOR, $selectedValues);
            return $selection;
        }, []);
    }

    /**
     * @param string $codeToGet
     * @param FilterNavigationFilterOption $filterOption
     * @return string[]
     */
    private function getUpdatedQueryParameterForFilterOption($codeToGet, FilterNavigationFilterOption $filterOption)
    {
        if ($codeToGet === $filterOption->getCode()) {
            return $this->toggleFilterOptionSelection($filterOption);
        }

        return $this->getSelectedFilterOptions($codeToGet);
    }

    /**
     * @param FilterNavigationFilterOption $filterOption
     * @return string[]
     */
    private function toggleFilterOptionSelection(FilterNavigationFilterOption $filterOption)
    {
        if ($this->isFilterOptionCurrentlySelected($filterOption)) {
            return $this->removeFilterOptionFromSelection($filterOption);
        }

        return $this->addFilterOptionToSelection($filterOption);
    }

    /**
     * @param FilterNavigationFilterOption $filterOption
     * @return bool
     */
    private function isFilterOptionCurrentlySelected(FilterNavigationFilterOption $filterOption)
    {
        return in_array($filterOption->getValue(), $this->getSelectedFilterOptions($filterOption->getCode()));
    }

    /**
     * @param FilterNavigationFilterOption $filterOption
     * @return string[]
     */
    private function removeFilterOptionFromSelection(FilterNavigationFilterOption $filterOption)
    {
        return array_diff($this->getSelectedFilterOptions($filterOption->getCode()), [$filterOption->getValue()]);
    }

    /**
     * @param FilterNavigationFilterOption $filterOption
     * @return string[]
     */
    private function addFilterOptionToSelection(FilterNavigationFilterOption $filterOption)
    {
        return array_merge($this->getSelectedFilterOptions($filterOption->getCode()), [$filterOption->getValue()]);
    }

    /**
     * @param string $filterCode
     * @return string[]
     */
    private function getSelectedFilterOptions($filterCode)
    {
        return $this->getSelectedFilters()[$filterCode];
    }

    /**
     * @param string[] $queryArguments
     * @return string
     */
    private function buildHttpQueryFromArray(array $queryArguments)
    {
        /* TODO: Once base URL is accessible in blocks maybe replace http_build_query w/ HttpUrl method */
        return http_build_query(array_filter($queryArguments));
    }

    /**
     * @return array[]
     */
    private function getSelectedFilters()
    {
        if (null === $this->lazyLoadedSelectedFilters) {
            $this->lazyLoadedSelectedFilters = $this->getFilterCollection()->getSelectedFilters();
        }

        return $this->lazyLoadedSelectedFilters;
    }
}
