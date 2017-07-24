<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\HttpUnknownMethodRequest;

class UnknownHttpRequestMethodHandler implements HttpRequestHandler
{
    const STATUSCODE_METHOD_NOT_ALLOWED = 405;

    public function canProcess(HttpRequest $request): bool
    {
        return $request instanceof HttpUnknownMethodRequest;
    }

    public function process(HttpRequest $request): HttpResponse
    {
        return GenericHttpResponse::create('Method not allowed', [], self::STATUSCODE_METHOD_NOT_ALLOWED);
    }
}
