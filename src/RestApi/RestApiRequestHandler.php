<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;

interface RestApiRequestHandler extends HttpRequestHandler
{
    public function canProcess(HttpRequest $request): bool;
}
