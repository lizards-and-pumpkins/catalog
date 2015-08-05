<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpRouter;

class ProductListingRouter implements HttpRouter
{
    /**
     * @var ProductListingRequestHandler
     */
    private $productListingRequestHandler;

    public function __construct(ProductListingRequestHandler $productListingRequestHandler)
    {
        $this->productListingRequestHandler = $productListingRequestHandler;
    }

    /**
     * @param HttpRequest $request
     * @param Context $context
     * @return HttpRequestHandler|null
     */
    public function route(HttpRequest $request, Context $context)
    {
        if (!$this->productListingRequestHandler->canProcess($request)) {
            return null;
        }
        
        return $this->productListingRequestHandler;
    }
}
