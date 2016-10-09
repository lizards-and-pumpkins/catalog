<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;

class NullApiRequestHandler extends ApiRequestHandler
{
    public function canProcess(HttpRequest $request) : bool
    {
        return false;
    }

    final protected function getResponse(HttpRequest $request) : HttpResponse
    {
        throw new \RuntimeException('NullApiRequestHandler should never be processed.');
    }
}
