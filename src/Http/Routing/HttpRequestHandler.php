<?php

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;

interface HttpRequestHandler
{
    /**
     * @param  HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request);

    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public function process(HttpRequest $request);
}
