<?php

namespace Brera\Product;

use Brera\Api\ApiRequestHandler;

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
     * @return string
     */
    protected final function getResponseBody()
    {
        return json_encode('dummy response');
    }
}
