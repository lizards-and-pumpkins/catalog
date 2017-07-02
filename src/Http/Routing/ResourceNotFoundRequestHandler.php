<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;

class ResourceNotFoundRequestHandler implements HttpRequestHandler
{
    const CODE = 'Something unique which will trigger default case of handler locator';

    public function process(HttpRequest $request) : HttpResponse
    {
        return new HttpResourceNotFoundResponse();
    }
}
