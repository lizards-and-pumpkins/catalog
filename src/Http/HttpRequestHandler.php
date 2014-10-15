<?php


namespace Brera\PoC;


interface HttpRequestHandler
{
    /**
     * @return HttpResponse
     */
    public function process();
} 
