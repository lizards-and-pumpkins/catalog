<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;

class NullApiRequestHandler implements RestApiRequestHandler
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
