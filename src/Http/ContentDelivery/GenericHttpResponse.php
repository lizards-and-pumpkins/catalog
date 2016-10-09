<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery;

use LizardsAndPumpkins\Http\ContentDelivery\Exception\InvalidStatusCodeException;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpResponse;

class GenericHttpResponse implements HttpResponse
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
     * @var int
     */
    private $statusCode;

    private function __construct(string $body, HttpHeaders $headers, int $statusCode)
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->statusCode = $statusCode;
    }

    /**
     * @param string $body
     * @param string[] $headers
     * @param int $statusCode
     * @return GenericHttpResponse
     */
    public static function create(string $body, array $headers, int $statusCode) : GenericHttpResponse
    {
        self::validateStatusCode($statusCode);

        $httpHeaders = HttpHeaders::fromArray($headers);

        return new self($body, $httpHeaders, $statusCode);
    }

    public function getBody() : string
    {
        return $this->body;
    }

    public function getStatusCode() : int
    {
        return $this->statusCode;
    }

    public function send()
    {
        http_response_code($this->statusCode);
        $this->sendHeaders();
        echo $this->getBody();
    }

    private function sendHeaders()
    {
        foreach ($this->headers->getAll() as $headerName => $headerValue) {
            header(sprintf('%s: %s', $headerName, $headerValue));
        }
    }

    private static function validateStatusCode(int $statusCode)
    {
        if (! self::isStatusCodeSupported($statusCode)) {
            throw new InvalidStatusCodeException(sprintf('Response status code %s is not supported.', $statusCode));
        }
    }

    private static function isStatusCodeSupported(int $code) : bool
    {
        return ($code >= 100 && $code <= 102) ||
               ($code >= 200 && $code <= 208) || $code === 226 ||
               ($code >= 300 && $code <= 308) ||
               ($code >= 400 && $code <= 417) || ($code >= 421 && $code <= 424) || $code === 426 ||
               ($code >= 428 && $code <= 429) || $code === 431 || $code === 451 ||
               ($code >= 500 && $code <= 511) || ($code >= 598 && $code <= 599);
    }
}
