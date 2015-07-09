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
     * @param string $name
     * @param string $value
     */
    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
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
        $this->sendHeaders();
        echo $this->getBody();
    }

    private function sendHeaders()
    {
        foreach ($this->headers as $headerName => $headerValue) {
            header(sprintf('%s: %s', $headerName, $headerValue));
        }
    }
}
