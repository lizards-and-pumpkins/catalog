<?php


namespace Brera\PoC;


class ProductSeoUrlRouter implements HttpRouter
{
    /**
     * @param HttpRequest $request
     * @return HttpRequestHandler
     */
    public function route(HttpRequest $request)
    {
        // get product if from data pool reader based on request url
        // pass product id to product detail page as constructor argument
        
        return new ProductDetailPage();
    }
} 