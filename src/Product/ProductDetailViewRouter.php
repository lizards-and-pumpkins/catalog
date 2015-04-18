<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpRouter;

class ProductDetailViewRouter implements HttpRouter
{
    /**
     * @var ProductDetailViewRequestHandlerBuilder
     */
    private $productDetailViewRequestHandlerBuilder;

    public function __construct(ProductDetailViewRequestHandlerBuilder $productDetailViewRequestHandlerBuilder)
    {
        $this->productDetailViewRequestHandlerBuilder = $productDetailViewRequestHandlerBuilder;
    }

    /**
     * @param HttpRequest $request
     * @param Context $context
     * @return HttpRequestHandler|null
     */
    public function route(HttpRequest $request, Context $context)
    {
        $handler = $this->productDetailViewRequestHandlerBuilder->create($request->getUrl(), $context);
        if (! $handler->canProcess()) {
            return null;
        }
        
        return $handler;
    }
}
