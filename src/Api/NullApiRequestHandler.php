<?php

namespace Brera\Api;

use Brera\Http\HttpRequest;

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
