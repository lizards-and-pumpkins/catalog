<?php

namespace Brera\Product;

use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpRouter;

class ProductSearchResultsRouter implements HttpRouter
{
    /**
     * @var ProductSearchRequestHandler
     */
    private $productSearchRequestHandler;

    public function __construct(ProductSearchRequestHandler $productSearchRequestHandler)
    {
        $this->productSearchRequestHandler = $productSearchRequestHandler;
    }

    /**
     * @param HttpRequest $request
     * @return HttpRequestHandler|null
     */
    public function route(HttpRequest $request)
    {
        if (!$this->productSearchRequestHandler->canProcess($request)) {
            return null;
        }
        
        return $this->productSearchRequestHandler;
    }
}
