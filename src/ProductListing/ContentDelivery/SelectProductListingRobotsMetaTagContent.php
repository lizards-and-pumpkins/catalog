<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Http\HttpRequest;

class SelectProductListingRobotsMetaTagContent
{
    public function getRobotsMetaTagContentForRequest(HttpRequest $request) : string
    {
        return $request->hasQueryParameters() ?
            'noindex' :
            'all';
    }
}
