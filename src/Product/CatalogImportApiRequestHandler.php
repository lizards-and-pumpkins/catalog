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
        // TODO: Implement canProcess() method.
    }

    /**
     * @return string
     */
    protected final function getResponseBody()
    {
        return json_encode('dummy response');
    }
}
