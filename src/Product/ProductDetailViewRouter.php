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

    public function __construct(ProductDetailViewRequestHandlerBuilder $urlKeyRequestHandlerBuilder)
    {
        $this->productDetailViewRequestHandlerBuilder = $urlKeyRequestHandlerBuilder;
    }

    /**
     * @param HttpRequest $request
     * @param Context $context
     * @return HttpRequestHandler|null
     */
    public function route(HttpRequest $request, Context $context)
    {
        $urlKeyRequestHandler = $this->productDetailViewRequestHandlerBuilder->create($request->getUrl(), $context);
        if (! $urlKeyRequestHandler->canProcess()) {
            return null;
        }
        
        return $urlKeyRequestHandler;
    }
}
