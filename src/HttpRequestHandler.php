<?php


namespace Brera\PoC;


interface HttpRequestHandler
{
    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public function process(HttpRequest $request);
} 