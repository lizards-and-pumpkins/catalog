<?php

namespace Brera\Product\Block;

use Brera\Product\FilterNavigationFilterCollection;
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
     * @param string $filterCode
     * @param string $filterValue
     * @return string
     */
    public function getQueryStringForFilterSelection($filterCode, $filterValue)
    {
        $queryArgumentsForSelection = $this->getQueryArgumentsForSelection($filterCode, $filterValue);
        return $this->buildHttpQueryFromArray($queryArgumentsForSelection);
    }

    /**
     * @param string $filterCode
     * @param string $filterValue
     * @return string[]
     */
    private function getQueryArgumentsForSelection($filterCode, $filterValue)
    {
        $filterCodes = array_keys($this->getSelectedFilters());
        return array_reduce($filterCodes, function (array $selection, $codeToCheck) use ($filterCode, $filterValue) {
            $selectedValues = $this->getUpdatedQueryValuesForFilter($codeToCheck, $filterCode, $filterValue);
            $selection[$codeToCheck] = implode(self::VALUES_SEPARATOR, $selectedValues);
            return $selection;
        }, []);
    }

    /**
     * @param string $codeToGet
     * @param string $codeToUpdate
     * @param string $filterValue
     * @return string[]
     */
    private function getUpdatedQueryValuesForFilter($codeToGet, $codeToUpdate, $filterValue)
    {
        if ($codeToGet === $codeToUpdate) {
            return $this->toggleValueSelectionForFilter($codeToUpdate, $filterValue);
        }

        return $this->getSelectedValuesForFilter($codeToGet);
    }

    /**
     * @param string $filterCode
     * @param string $filterValue
     * @return string[]
     */
    private function toggleValueSelectionForFilter($filterCode, $filterValue)
    {
        if ($this->isFilterValueCurrentlySelected($filterCode, $filterValue)) {
            return $this->removeValueFromFilterSelection($filterCode, $filterValue);
        }

        return $this->addValueToFilterSelection($filterCode, $filterValue);
    }

    /**
     * @param string $filterCode
     * @param string $filterValue
     * @return bool
     */
    private function isFilterValueCurrentlySelected($filterCode, $filterValue)
    {
        return in_array($filterValue, $this->getValuesForFilter($filterCode));
    }

    /**
     * @param string $filterCode
     * @param string $filterValue
     * @return string[]
     */
    private function removeValueFromFilterSelection($filterCode, $filterValue)
    {
        return array_diff($this->getValuesForFilter($filterCode), [$filterValue]);
    }

    /**
     * @param string $filterCode
     * @param string $filterValue
     * @return string[]
     */
    private function addValueToFilterSelection($filterCode, $filterValue)
    {
        return array_merge($this->getValuesForFilter($filterCode), [$filterValue]);
    }

    /**
     * @param string $filterCode
     * @return string[]
     */
    private function getSelectedValuesForFilter($filterCode)
    {
        return $this->getSelectedFilters()[$filterCode];
    }

    /**
     * @param string $filterCode
     * @return string[]
     */
    private function getValuesForFilter($filterCode)
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
