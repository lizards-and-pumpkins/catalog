<?php

namespace LizardsAndPumpkins\RestApi;

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

    final protected function getResponse(HttpRequest $request)
    {
        throw new \RuntimeException('NullApiRequestHandler should never be processed.');
    }
}
