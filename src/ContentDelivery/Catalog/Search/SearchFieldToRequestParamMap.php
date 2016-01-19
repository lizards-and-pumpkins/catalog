<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\Search;

use LizardsAndPumpkins\ContentDelivery\Catalog\Search\Exception\InvalidSearchFieldToQueryParameterMapException;

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
    private function validateArrayMap(array $map, $nameInExceptions)
    {
        array_map(function ($key, $value) use ($nameInExceptions) {
            $this->validateArrayKey($key, $nameInExceptions);
            $this->validateArrayValue($value, $nameInExceptions);
        }, array_keys($map), $map);
    }

    /**
     * @param mixed $arrayKey
     * @param string $nameInException
     */
    private function validateArrayKey($arrayKey, $nameInException)
    {
        if (!is_string($arrayKey)) {
            throw $this->createInvalidArrayKeyTypeException($arrayKey, $nameInException);
        }
        if ('' === $arrayKey) {
            $message = sprintf('The %s Map must have not have empty string keys', $nameInException);
            throw new InvalidSearchFieldToQueryParameterMapException($message);
        }
    }

    /**
     * @param mixed $arrayValue
     * @param string $nameInException
     */
    private function validateArrayValue($arrayValue, $nameInException)
    {
        if (!is_string($arrayValue)) {
            throw $this->createInvalidValueTypeException($arrayValue, $nameInException);
        }
        if ('' === $arrayValue) {
            $message = sprintf('The %s Map must have not have empty string values', $nameInException);
            throw new InvalidSearchFieldToQueryParameterMapException($message);
        }
    }

    /**
     * @param mixed $arrayKey
     * @param string $nameInException
     * @return InvalidSearchFieldToQueryParameterMapException
     */
    private function createInvalidArrayKeyTypeException($arrayKey, $nameInException)
    {
        $message = sprintf('The %s Map must have string keys, got "%s"', $nameInException, $arrayKey);
        return new InvalidSearchFieldToQueryParameterMapException($message);
    }

    /**
     * @param mixed $arrayValue
     * @param string $nameInException
     * @return InvalidSearchFieldToQueryParameterMapException
     */
    private function createInvalidValueTypeException($arrayValue, $nameInException)
    {
        $type = $this->getType($arrayValue);
        $message = sprintf('The %s Map must have string values, got "%s"', $nameInException, $type);
        return new InvalidSearchFieldToQueryParameterMapException($message);
    }

    /**
     * @param string $requestParameterName
     * @return string
     */
    public function getSearchFieldName($requestParameterName)
    {
        return $this->getFieldValueOrDefault($this->queryParametersToFacetFields, $requestParameterName);
    }

    /**
     * @param string $searchFieldName
     * @return string
     */
    public function getQueryParameterName($searchFieldName)
    {
        return $this->getFieldValueOrDefault($this->searchFieldsToQueryParameters, $searchFieldName);
    }

    /**
     * @param string[] $array
     * @param string $key
     * @return string
     */
    private function getFieldValueOrDefault($array, $key)
    {
        return isset($array[$key]) ?
            $array[$key] :
            $key;
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private function getType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }
}
