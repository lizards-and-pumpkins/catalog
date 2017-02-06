<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;

class StubEmptyApiRequestHandler extends ApiRequestHandler
{
    final public function canProcess(HttpRequest $request) : bool
    {
        return true;
    }

    final protected function getResponse(HttpRequest $request) : HttpResponse
    {
        return GenericHttpResponse::create($body = '', $headers = [], HttpResponse::STATUS_OK);
    }
}
