<?php

namespace Brera\Http;

use Brera\Environment\Environment;

class HttpRouterChain implements HttpRouter
{
    /**
     * @var HttpRouter[]
     */
    private $routers = [];

    /**
     * @param HttpRequest $request
     * @param Environment $environment
     * @return HttpRequestHandler
     */
    public function route(HttpRequest $request, Environment $environment)
    {
        foreach ($this->routers as $router) {
            $handler = $router->route($request, $environment);
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
