<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;

class NullApiRequestHandler implements HttpRequestHandler
{
    public function canProcess(HttpRequest $request) : bool
    {
        return false;
    }

    public function process(HttpRequest $request): HttpResponse
    {
        throw new \RuntimeException('NullApiRequestHandler should never be processed.');
    }
}
