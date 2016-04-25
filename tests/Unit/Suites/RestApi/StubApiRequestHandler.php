<?php

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;

class StubApiRequestHandler extends ApiRequestHandler
{
    const DUMMY_BODY_CONTENT = 'dummy';

    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        return true;
    }

    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    protected function getResponse(HttpRequest $request)
    {
        $headers = [];
        $statusCode = 200;

        return GenericHttpResponse::create(self::DUMMY_BODY_CONTENT, $headers, $statusCode);
    }
}
