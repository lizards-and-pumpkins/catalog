<?php

namespace Brera\Product;

use Brera\DefaultHttpResponse;
use Brera\Http\HttpRequestHandler;

class CatalogImportApiRequestHandler implements HttpRequestHandler
{
    /**
     * @return DefaultHttpResponse
     */
    public function process()
    {
        // todo: change to json response
        $response = new DefaultHttpResponse();
        $response->setBody(json_encode('dummy response'));
        return $response;
    }

    /**
     * @return bool
     */
    public function canProcess()
    {
        // TODO: Implement canProcess() method.
    }
}
