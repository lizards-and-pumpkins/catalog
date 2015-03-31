<?php

namespace Brera\Http;

use Brera\Context\Context;

class HttpRouterChain implements HttpRouter
{
    /**
     * @var HttpRouter[]
     */
    private $routers = [];

    /**
     * @param HttpRequest $request
     * @param Context $context
     * @return HttpRequestHandler
     * @throws UnableToRouteRequestException
     */
    public function route(HttpRequest $request, Context $context)
    {
        foreach ($this->routers as $router) {
            $handler = $router->route($request, $context);
            if (null !== $handler) {
                return $handler;
            }
        }
        throw new UnableToRouteRequestException(sprintf('Unable to route a request "%s"', $request->getUrl()));
    }

    public function register(HttpRouter $router)
    {
        $this->routers[] = $router;
    }
}
