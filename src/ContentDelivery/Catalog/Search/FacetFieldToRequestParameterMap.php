<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\Search;

interface FacetFieldToRequestParameterMap
{
    /**
     * @param string $requestParameterName
     * @return string
     */
    public function getFacetFieldName($requestParameterName);

    /**
     * @param string $facetFieldName
     * @return string
     */
    public function getQueryParameterName($facetFieldName);
}
