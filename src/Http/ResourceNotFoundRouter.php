<?php


namespace Brera\Http;

use Brera\Environment\Environment;

class ResourceNotFoundRouter implements HttpRouter
{
    /**
     * @param HttpRequest $request
     * @param Environment $environment
     * @return HttpRequestHandler
     */
    public function route(HttpRequest $request, Environment $environment)
    {
        return new ResourceNotFoundRequestHandler();
    }
}
