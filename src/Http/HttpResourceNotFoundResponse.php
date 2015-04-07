<?php


namespace Brera\Http;

class HttpResourceNotFoundResponse implements HttpResponse
{
    /**
     * @return string
     */
    public function getBody()
    {
        return '<h1>404 Resource not found</h1>';
    }

    public function send()
    {
        http_response_code(404);
        echo $this->getBody();
    }
}
