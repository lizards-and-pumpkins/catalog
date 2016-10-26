<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Http\HttpResponse;

class HttpResourceNotFoundResponse implements HttpResponse
{
    public function getBody() : string
    {
        return '<h1>404 Resource not found</h1>';
    }

    public function getStatusCode() : int
    {
        return HttpResponse::STATUS_NOT_FOUND;
    }

    public function send()
    {
        http_response_code($this->getStatusCode());
        echo $this->getBody();
    }
}
