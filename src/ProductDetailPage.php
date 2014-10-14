<?php


namespace Brera\PoC;


class ProductDetailPage implements HttpRequestHandler
{
    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public function process(HttpRequest $request)
    {
        return new DefaultHttpResponse();
    }

} 