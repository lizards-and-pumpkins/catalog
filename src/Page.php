<?php

namespace Brera;

use Brera\Http\HttpResponse;

class Page implements HttpResponse
{
    private $body;

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sends headers, cookies and afterwards the body to the client
     *
     * @return null
     */
    public function send()
    {
        echo $this->body;
    }
}
