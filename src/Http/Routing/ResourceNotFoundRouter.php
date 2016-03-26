<?php

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\HttpRequest;

class ResourceNotFoundRouter implements HttpRouter
{
    /**
     * @param HttpRequest $request
     * @return HttpRequestHandler
     */
    public function route(HttpRequest $request)
    {
        return new ResourceNotFoundRequestHandler();
    }
}
