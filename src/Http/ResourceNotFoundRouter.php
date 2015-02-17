<?php


namespace Brera\Http;

use Brera\Context\Context;

class ResourceNotFoundRouter implements HttpRouter
{
    /**
     * @param HttpRequest $request
     * @param Context $context
     * @return HttpRequestHandler
     */
    public function route(HttpRequest $request, Context $context)
    {
        return new ResourceNotFoundRequestHandler();
    }
}
