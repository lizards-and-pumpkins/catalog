<?php

namespace Brera\Http;

interface HttpRequestHandler
{
    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request);

    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public function process(HttpRequest $request);
}
