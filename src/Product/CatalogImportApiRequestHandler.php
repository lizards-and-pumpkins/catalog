<?php

namespace Brera\Product;

use Brera\DefaultHttpResponse;
use Brera\Http\HttpRequestHandler;

class CatalogImportApiRequestHandler implements HttpRequestHandler
{
    public function process()
    {
        // todo: change to json response
        $response = new DefaultHttpResponse();
        $response->setBody(json_encode('dummy response'));
        return $response;
    }
}
