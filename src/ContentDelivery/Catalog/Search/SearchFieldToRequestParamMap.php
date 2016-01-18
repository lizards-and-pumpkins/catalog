<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\Search;

class SearchFieldToRequestParamMap
{
    /**
     * @var string[]
     */
    private $facetFieldsToQueryParameters;

    /**
     * @var string[]
     */
    private $queryParametersToFacetFields;

    /**
     * @param string[] $facetFieldToQueryParameterMap
     * @param string[] $queryParameterToFacetFieldMap
     */
    public function __construct(
        array $facetFieldToQueryParameterMap,
        array $queryParameterToFacetFieldMap
    ) {
        $this->facetFieldsToQueryParameters = $facetFieldToQueryParameterMap;
        $this->queryParametersToFacetFields = $queryParameterToFacetFieldMap;
    }
    
    /**
     * @param string $requestParameterName
     * @return string
     */
    public function getFacetFieldName($requestParameterName)
    {
        return isset($this->queryParametersToFacetFields[$requestParameterName]) ?
            $this->queryParametersToFacetFields[$requestParameterName] :
            $requestParameterName;
    }

    /**
     * @param string $facetFieldName
     * @return string
     */
    public function getQueryParameterName($facetFieldName)
    {
        return isset($this->facetFieldsToQueryParameters[$facetFieldName]) ?
            $this->facetFieldsToQueryParameters[$facetFieldName] :
            $facetFieldName;
    }
}
