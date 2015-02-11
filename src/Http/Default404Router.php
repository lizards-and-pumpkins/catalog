<?php


namespace Brera\Http;

use Brera\Environment\Environment;

class Default404Router implements HttpRouter
{
    /**
     * @param HttpRequest $request
     * @param Environment $environment
     * @return HttpRequestHandler
     */
    public function route(HttpRequest $request, Environment $environment)
    {
        return new Default404RequestHandler();
    }
}
