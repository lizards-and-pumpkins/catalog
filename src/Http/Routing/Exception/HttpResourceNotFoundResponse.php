<?php

namespace LizardsAndPumpkins\Http\Routing\Exception;

use LizardsAndPumpkins\Http\HttpResponse;

class HttpResourceNotFoundResponse implements HttpResponse
{
    /**
     * @return string
     */
    public function getBody()
    {
        return '<h1>404 Resource not found</h1>';
    }

    public function getStatusCode()
    {
        return HttpResponse::STATUS_NOT_FOUND;
    }

    public function send()
    {
        http_response_code($this->getStatusCode());
        echo $this->getBody();
    }
}
