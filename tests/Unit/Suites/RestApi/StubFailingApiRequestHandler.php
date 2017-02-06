<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;

class StubFailingApiRequestHandler extends ApiRequestHandler
{
    const EXCEPTION_MESSAGE = 'foo';

    final public function canProcess(HttpRequest $request) : bool
    {
        return true;
    }

    final protected function getResponse(HttpRequest $request) : HttpResponse
    {
        throw new \Exception(self::EXCEPTION_MESSAGE);
    }
}
