<?php

namespace Brera\Product;

use Brera\Http\HttpRequestHandler;

class CatalogImportApiRequestHandler implements HttpRequestHandler
{
    public function process()
    {
        return json_encode('dummy response');
    }
}
