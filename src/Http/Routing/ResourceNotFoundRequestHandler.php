<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;

class ResourceNotFoundRequestHandler implements HttpRequestHandler
{
    public function process(HttpRequest $request) : HttpResponse
    {
        return new HttpResourceNotFoundResponse();
    }

    public function canProcess(HttpRequest $request) : bool
    {
        return true;
    }
}
