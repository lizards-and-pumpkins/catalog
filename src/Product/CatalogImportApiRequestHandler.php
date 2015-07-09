<?php

namespace Brera\Product;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;

class CatalogImportApiRequestHandler extends ApiRequestHandler
{
    /**
     * @return bool
     */
    public final function canProcess()
    {
        return true;
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    protected final function getResponseBody(HttpRequest $request)
    {

        return json_encode('dummy response');
    }
}
