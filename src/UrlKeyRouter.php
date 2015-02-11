<?php


namespace Brera;

use Brera\Environment\Environment;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpRouter;

class UrlKeyRouter implements HttpRouter
{
    /**
     * @var UrlKeyRequestHandlerBuilder
     */
    private $urlKeyRequestHandlerBuilder;

    public function __construct(UrlKeyRequestHandlerBuilder $urlKeyRequestHandlerBuilder)
    {
        $this->urlKeyRequestHandlerBuilder = $urlKeyRequestHandlerBuilder;
    }

    /**
     * @param HttpRequest $request
     * @param Environment $environment
     * @return HttpRequestHandler|null
     */
    public function route(HttpRequest $request, Environment $environment)
    {
        $urlKeyRequestHandler = $this->urlKeyRequestHandlerBuilder->create($request->getUrl(), $environment);
        if (! $urlKeyRequestHandler->canProcess()) {
            return null;
        }
        
        return $urlKeyRequestHandler;
    }
}
