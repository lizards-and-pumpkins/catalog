<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\HttpRequest;

class ResourceNotFoundRouter implements HttpRouter
{
    public function route(HttpRequest $request) : HttpRequestHandler
    {
        return new ResourceNotFoundRequestHandler();
    }
}
