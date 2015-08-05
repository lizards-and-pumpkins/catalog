<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpRouter;

class ProductDetailViewRouter implements HttpRouter
{
    /**
     * @var ProductDetailViewRequestHandler
     */
    private $productDetailViewRequestHandler;

    public function __construct(ProductDetailViewRequestHandler $productDetailViewRequestHandler)
    {
        $this->productDetailViewRequestHandler = $productDetailViewRequestHandler;
    }

    /**
     * @param HttpRequest $request
     * @param Context $context
     * @return HttpRequestHandler|null
     */
    public function route(HttpRequest $request, Context $context)
    {
        if (!$this->productDetailViewRequestHandler->canProcess($request)) {
            return null;
        }
        
        return $this->productDetailViewRequestHandler;
    }
}
