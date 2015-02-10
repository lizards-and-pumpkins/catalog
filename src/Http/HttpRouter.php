<?php

namespace Brera\Http;

use Brera\Environment\Environment;

interface HttpRouter
{
    /**
     * @param HttpRequest $request
     * @param Environment $environment
     * @return HttpRequestHandler
     */
    public function route(HttpRequest $request, Environment $environment);
}
