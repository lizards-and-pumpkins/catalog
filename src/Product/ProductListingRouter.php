<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpRouter;

class ProductListingRouter implements HttpRouter
{
    /**
     * @var ProductListingRequestHandlerBuilder
     */
    private $urlKeyRequestHandlerBuilder;

    public function __construct(ProductListingRequestHandlerBuilder $urlKeyRequestHandlerBuilder)
    {
        $this->urlKeyRequestHandlerBuilder = $urlKeyRequestHandlerBuilder;
    }

    /**
     * @param HttpRequest $request
     * @param Context $context
     * @return HttpRequestHandler|null
     */
    public function route(HttpRequest $request, Context $context)
    {
        $urlKeyRequestHandler = $this->urlKeyRequestHandlerBuilder->create($request->getUrl(), $context);
        if (! $urlKeyRequestHandler->canProcess($request)) {
            return null;
        }
        
        return $urlKeyRequestHandler;
    }
}
