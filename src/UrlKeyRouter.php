<?php


namespace Brera;

use Brera\Context\Context;
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
     * @param Context $context
     * @return HttpRequestHandler|null
     */
    public function route(HttpRequest $request, Context $context)
    {
        $urlKeyRequestHandler = $this->urlKeyRequestHandlerBuilder->create($request->getUrl(), $context);
        if (! $urlKeyRequestHandler->canProcess()) {
            return null;
        }
        
        return $urlKeyRequestHandler;
    }
}
