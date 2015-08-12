<?php

namespace Brera\Http;

use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpRouter;

class GenericHttpRouter implements HttpRouter
{
    /**
     * @var HttpRequestHandler
     */
    private $requestHandler;

    public function __construct(HttpRequestHandler $requestHandler)
    {
        $this->requestHandler = $requestHandler;
    }

    /**
     * @param HttpRequest $request
     * @return HttpRequestHandler|null
     */
    public function route(HttpRequest $request)
    {
        if (!$this->requestHandler->canProcess($request)) {
            return null;
        }
        
        return $this->requestHandler;
    }
}
