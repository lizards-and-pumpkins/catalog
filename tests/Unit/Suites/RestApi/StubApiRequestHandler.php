<?php

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;

class StubApiRequestHandler extends ApiRequestHandler
{
    const DUMMY_BODY_CONTENT = 'dummy';

    final public function canProcess(HttpRequest $request) : bool
    {
        return true;
    }

    final protected function getResponse(HttpRequest $request) : HttpResponse
    {
        $headers = [];

        return GenericHttpResponse::create(self::DUMMY_BODY_CONTENT, $headers, HttpResponse::STATUS_OK);
    }
}
