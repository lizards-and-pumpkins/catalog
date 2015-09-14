<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\InvalidResponseBodyException;

class DefaultHttpResponse implements HttpResponse
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var HttpHeaders
     */
    private $headers;

    /**
     * @param string $body
     * @param HttpHeaders $headers
     */
    private function __construct($body, HttpHeaders $headers)
    {
        $this->body = $body;
        $this->headers = $headers;
    }

    /**
     * @param string $body
     * @param string[] $headers
     * @return DefaultHttpResponse
     */
    public static function create($body, array $headers)
    {
        if (!is_string($body)) {
            throw new InvalidResponseBodyException(
                sprintf('Expecting request body to be string, got %s', gettype($body))
            );
        }

        $httpHeaders = HttpHeaders::fromArray($headers);

        return new self($body, $httpHeaders);
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
        foreach ($this->headers->getAll() as $headerName => $headerValue) {
            header(sprintf('%s: %s', $headerName, $headerValue));
        }
    }
}
