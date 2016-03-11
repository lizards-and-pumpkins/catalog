<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Http\HttpRequest;

class SelectProductListingRobotsMetaTagContent
{
    /**
     * @param HttpRequest $request
     * @return string
     */
    public function getRobotsMetaTagContentForRequest(HttpRequest $request)
    {
        return $request->hasQueryParameters() ?
            'noindex' :
            'all';
    }
}
