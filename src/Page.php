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
     * Sends headers, cookies and afterwards the body to the client
     *
     * @return null
     */
    public function send()
    {
        echo $this->body;
    }
}
