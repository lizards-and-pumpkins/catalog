<?php

namespace Brera\PoC\Http;

interface HttpRequestHandler
{
    /**
     * @return HttpResponse
     */
    public function process();
} 
