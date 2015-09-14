<?php

namespace LizardsAndPumpkins\Api;

use LizardsAndPumpkins\Http\HttpRequest;

class NullApiRequestHandler extends ApiRequestHandler
{
    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        return false;
    }

    protected function getResponseBody(HttpRequest $request)
    {
        throw new \RuntimeException('NullApiRequestHandler should never be processed.');
    }
}
