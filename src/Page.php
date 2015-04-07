<?php

namespace Brera;

use Brera\Http\HttpResponse;

class Page implements HttpResponse
{
    /**
     * @var string
     */
    private $body;

    /**
     * @param string $body
     */
    public function __construct($body)
    {
        if (!is_string($body)) {
            throw new \InvalidArgumentException(
                sprintf('Parameter needs to be string, instead %s was passed.', gettype($body))
            );
        }
        $this->body = $body;
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
        $this->sendBody();
    }

    private function sendHeaders()
    {
    }

    private function sendBody()
    {
        echo $this->body;
    }
}
