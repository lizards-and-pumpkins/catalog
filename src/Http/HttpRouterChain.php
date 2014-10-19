<?php

namespace Brera\PoC\Http;

class HttpRouterChain implements HttpRouter
{
    /**
     * @var HttpRouter[]
     */
    private $routers;

    /**
     * @param HttpRequest $request
     * @return HttpRequestHandler
     * @throws UnableToRouteRequestException
     */
    public function route(HttpRequest $request)
    {
        foreach ($this->routers as $router) {
            $handler = $router->route($request);
            if (null !== $handler) {
                return $handler;
            }
        }
        throw new UnableToRouteRequestException(sprintf('Unable to route a request "%s"', $request->getUrl()));
    }

    /**
     * @param HttpRouter $router
     * @return null
     */
    public function register(HttpRouter $router)
    {
        $this->routers[] = $router;
    }
} 
