<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidFilterNavigationFilterCodeException;

class FilterNavigationFilter implements \JsonSerializable
{
    /**
     * @var string
     */
    private $filterCode;

    /**
     * @var FilterNavigationFilterOptionCollection
     */
    private $filterOptionCollection;

    /**
     * @param string $filterCode
     * @param FilterNavigationFilterOptionCollection $filterOptionCollection
     */
    private function __construct($filterCode, FilterNavigationFilterOptionCollection $filterOptionCollection)
    {
        $this->filterCode = $filterCode;
        $this->filterOptionCollection = $filterOptionCollection;
    }

    /**
     * @param string $filterCode
     * @param FilterNavigationFilterOptionCollection $filterOptionCollection
     * @return FilterNavigationFilter
     */
    public static function create($filterCode, FilterNavigationFilterOptionCollection $filterOptionCollection)
    {
        if (!is_string($filterCode)) {
            throw new InvalidFilterNavigationFilterCodeException(
                sprintf('Filter code must be a string, got "%s".', gettype($filterCode))
            );
        }

        return new self($filterCode, $filterOptionCollection);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->filterCode;
    }

    /**
     * @return FilterNavigationFilterOptionCollection
     */
    public function getOptionCollection()
    {
        return $this->filterOptionCollection;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            'code' => $this->filterCode,
            'options' => $this->filterOptionCollection->jsonSerialize()
        ];
    }
}
