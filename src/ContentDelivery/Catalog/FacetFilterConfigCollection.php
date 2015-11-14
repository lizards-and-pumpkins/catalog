<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

class FacetFilterConfigCollection implements \IteratorAggregate
{
    /**
     * @var string[]
     */
    private $lazyLoadedAttributeCodes;

    /**
     * @var FacetFilterConfig[]
     */
    private $facetFilterConfigs;

    public function __construct(FacetFilterConfig ...$facetFilterConfigs)
    {
        $this->facetFilterConfigs = $facetFilterConfigs;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->facetFilterConfigs);
    }

    /**
     * @return FacetFilterConfig[]
     */
    public function getConfigs()
    {
        return $this->facetFilterConfigs;
    }

    /**
     * @return string[]
     */
    public function getAttributeCodes()
    {
        if (null === $this->lazyLoadedAttributeCodes) {
            $this->lazyLoadedAttributeCodes = array_map(function (FacetFilterConfig $facetFilterConfig) {
                return (string) $facetFilterConfig->getAttributeCode();
            }, $this->facetFilterConfigs);
        }

        return $this->lazyLoadedAttributeCodes;
    }
}
