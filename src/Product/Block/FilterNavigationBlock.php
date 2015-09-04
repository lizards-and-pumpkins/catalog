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
        return 'object' !== gettype($variable) ? gettype($variable) : get_class($variable);
    }

    /**
     * @param string $filterCode
     * @param string $filterValue
     * @return string
     */
    public function getQueryString($filterCode, $filterValue)
    {
        $selectedFilters = $this->getSelectedFilters();
        foreach ($selectedFilters as $selectedFilterCode => $selectedValues) {
            if ($filterCode === $selectedFilterCode) {
                if (in_array($filterValue, $selectedValues)) {
                    $selectedValues = array_diff($selectedValues, [$filterValue]);
                } else {
                    $selectedValues[] = $filterValue;
                }
            }
            $selectedFilters[$selectedFilterCode] = implode(self::VALUES_SEPARATOR, $selectedValues);
        }

        /* TODO: Replace http_build_query w/ HttpUrl method */
        return http_build_query(array_filter($selectedFilters));
    }

    private function getSelectedFilters()
    {
        if (null === $this->lazyLoadedSelectedFilters) {
            $this->lazyLoadedSelectedFilters = $this->getFilterCollection()->getSelectedFilters();
        }

        return $this->lazyLoadedSelectedFilters;
    }
}
