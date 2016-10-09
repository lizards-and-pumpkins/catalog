<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\ProductSearch\Exception\InvalidSearchFieldToQueryParameterMapException;

class SearchFieldToRequestParamMap
{
    /**
     * @var string[]
     */
    private $searchFieldsToQueryParameters;

    /**
     * @var string[]
     */
    private $queryParametersToFacetFields;

    /**
     * @param string[] $searchFieldToQueryParameterMap
     * @param string[] $queryParameterToFacetFieldMap
     */
    public function __construct(
        array $searchFieldToQueryParameterMap,
        array $queryParameterToFacetFieldMap
    ) {
        $this->validateSearchFieldToQueryParameterMap($searchFieldToQueryParameterMap);
        $this->validateQueryParameterToSearchFieldMap($queryParameterToFacetFieldMap);
        
        $this->searchFieldsToQueryParameters = $searchFieldToQueryParameterMap;
        $this->queryParametersToFacetFields = $queryParameterToFacetFieldMap;
    }

    /**
     * @param string[] $searchFieldToQueryParameterMap
     */
    private function validateSearchFieldToQueryParameterMap(array $searchFieldToQueryParameterMap)
    {
        $this->validateArrayMap($searchFieldToQueryParameterMap, 'Search Field to Query Parameter');
    }

    /**
     * @param string[] $queryParameterToFacetFieldMap
     */
    private function validateQueryParameterToSearchFieldMap(array $queryParameterToFacetFieldMap)
    {
        $this->validateArrayMap($queryParameterToFacetFieldMap, 'Query Parameter to Search Field');
    }
    
    /**
     * @param string[] $map
     * @param string $nameInExceptions
     */
    private function validateArrayMap(array $map, string $nameInExceptions)
    {
        every($map, function (string $value, string $key) use ($nameInExceptions) {
            $this->validateArrayKey($key, $nameInExceptions);
            $this->validateArrayValue($value, $nameInExceptions);
        });
    }

    private function validateArrayKey(string $arrayKey, string $nameInException)
    {
        if ('' === $arrayKey) {
            $message = sprintf('The %s Map must have not have empty string keys', $nameInException);
            throw new InvalidSearchFieldToQueryParameterMapException($message);
        }
    }

    private function validateArrayValue(string $arrayValue, string $nameInException)
    {
        if ('' === $arrayValue) {
            $message = sprintf('The %s Map must have not have empty string values', $nameInException);
            throw new InvalidSearchFieldToQueryParameterMapException($message);
        }
    }

    public function getSearchFieldName(string $requestParameterName) : string
    {
        return $this->getFieldValueOrDefault($this->queryParametersToFacetFields, $requestParameterName);
    }

    public function getQueryParameterName(string $searchFieldName) : string
    {
        return $this->getFieldValueOrDefault($this->searchFieldsToQueryParameters, $searchFieldName);
    }

    /**
     * @param string[] $array
     * @param string $key
     * @return string
     */
    private function getFieldValueOrDefault(array $array, string $key) : string
    {
        return isset($array[$key]) ?
            $array[$key] :
            $key;
    }
}
