<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\HttpRequest;

interface HttpRouter
{
    /**
     * @param HttpRequest $request
     * @return HttpRequestHandler|null
     */
    public function route(HttpRequest $request);
}
