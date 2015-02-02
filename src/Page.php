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
     * @return Page
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

    /**
     * @return void
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendBody();
    }

    /**
     * @return void
     */
    private function sendHeaders()
    {
    }

    /**
     * @return void
     */
    private function sendBody()
    {
        echo $this->body;
    }
}
