<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;

class UnknownHttpRequestMethodHandler implements HttpRequestHandler
{
    const STATUSCODE_METHOD_NOT_ALLOWED = 405;
    const CODE = 'unknown_http_request';

    public function process(HttpRequest $request): HttpResponse
    {
        return GenericHttpResponse::create('Method not allowed', [], self::STATUSCODE_METHOD_NOT_ALLOWED);
    }
}
