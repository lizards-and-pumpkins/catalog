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
        array_map(function ($searchFieldName, $queryParameterName) {
            $name = 'Search Field to Query Parameter';
            $this->validateArrayKeys($searchFieldName, $name);
            $this->validateArrayValues($queryParameterName, $name);
        }, array_keys($searchFieldToQueryParameterMap), $searchFieldToQueryParameterMap);
        array_map(function ($queryParameterName, $searchFieldName) {
            $name = 'Query Parameter to Search Field';
            $this->validateArrayKeys($queryParameterName, $name);
            $this->validateArrayValues($searchFieldName, $name);
        }, array_keys($queryParameterToFacetFieldMap), $queryParameterToFacetFieldMap);
        $this->searchFieldsToQueryParameters = $searchFieldToQueryParameterMap;
        $this->queryParametersToFacetFields = $queryParameterToFacetFieldMap;
    }

    /**
     * @param mixed $searchFieldName
     * @param string $name
     */
    private function validateArrayKeys($searchFieldName, $name)
    {
        if (!is_string($searchFieldName)) {
            throw $this->createInvalidArrayKeyTypeException($searchFieldName, $name);
        }
        if ('' === $searchFieldName) {
            $message = sprintf('The %s Map must have not have empty string keys', $name);
            throw new InvalidSearchFieldToQueryParameterMapException($message);
        }
    }

    /**
     * @param mixed $queryParameterName
     * @param string $name
     */
    private function validateArrayValues($queryParameterName, $name)
    {
        if (!is_string($queryParameterName)) {
            throw $this->createInvalidValueTypeException($queryParameterName, $name);
        }
        if ('' === $queryParameterName) {
            $message = sprintf('The %s Map must have not have empty string values', $name);
            throw new InvalidSearchFieldToQueryParameterMapException($message);
        }
    }

    /**
     * @param mixed $searchFieldName
     * @param string $name
     * @return InvalidSearchFieldToQueryParameterMapException
     */
    private function createInvalidArrayKeyTypeException($searchFieldName, $name)
    {
        $message = sprintf('The %s Map must have string keys, got "%s"', $name, $searchFieldName);
        return new InvalidSearchFieldToQueryParameterMapException($message);
    }

    /**
     * @param mixed $queryParameterName
     * @param string $name
     * @return InvalidSearchFieldToQueryParameterMapException
     */
    private function createInvalidValueTypeException($queryParameterName, $name)
    {
        $message = sprintf('The %s Map must have string values, got "%s"', $name, $this->getType($queryParameterName));
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
