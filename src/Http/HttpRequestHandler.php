<?php

namespace Brera\Http;

interface HttpRequestHandler
{
    /**
     * @return bool
     */
    public function canProcess();

    /**
     * @return HttpResponse
     */
    public function process();
}
