<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentDelivery\Catalog\Search\FacetFieldToRequestParameterMap;

class IntegrationTestFacetFieldToRequestParameterMap implements FacetFieldToRequestParameterMap
{
    /**
     * @param string $requestParameterName
     * @return string
     */
    public function getFacetFieldName($requestParameterName)
    {
        return $requestParameterName;
    }

    /**
     * @param string $facetFieldName
     * @return string
     */
    public function getQueryParameterName($facetFieldName)
    {
        return $facetFieldName;
    }
}
