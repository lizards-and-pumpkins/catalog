<?php


namespace Brera\PoC;


interface HttpRouter
{
    /**
     * @param HttpRequest $request
     * @return HttpRequestHandler
     */
    public function route(HttpRequest $request);
} 