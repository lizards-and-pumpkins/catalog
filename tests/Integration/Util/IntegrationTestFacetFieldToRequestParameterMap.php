<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentDelivery\Catalog\Search\SearchFieldToRequestParamMap;

class IntegrationTestSearchFieldToRequestParamMap implements SearchFieldToRequestParamMap
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
