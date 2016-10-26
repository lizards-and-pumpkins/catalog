<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\HttpRequest;

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
