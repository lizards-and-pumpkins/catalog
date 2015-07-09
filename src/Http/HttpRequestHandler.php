<?php

namespace Brera\Http;

interface HttpRequestHandler
{
    /**
     * @return bool
     */
    public function canProcess();

    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public function process(HttpRequest $request);
}
