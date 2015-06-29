<?php

namespace Brera;

use Brera\Http\HttpResponse;

class DefaultHttpResponse implements HttpResponse
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var string[]
     */
    private $headers = [];

    /**
     * @param string $content
     * @return null
     */
    public function setBody($content)
    {
        $this->body = $content;
    }

    /**
     * @param string $header
     */
    public function addHeader($header)
    {
        $this->headers[] = $header;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    public function send()
    {
        array_map('header', $this->headers);
        echo $this->getBody();
    }
}
