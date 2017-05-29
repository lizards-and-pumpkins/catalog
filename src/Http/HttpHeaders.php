<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Exception\HeaderNotPresentException;
use LizardsAndPumpkins\Http\Exception\InvalidHttpHeadersException;

class HttpHeaders
{
    /**
     * @var string[]
     */
    private $headers;

    /**
     * @param string[] $headers
     */
    private function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @param string[] $headers
     * @return HttpHeaders
     */
    public static function fromArray(array $headers): HttpHeaders
    {
        $normalizedHeaders = [];

        foreach ($headers as $headerName => $headerValue) {
            if (! is_string($headerName) || ! is_string($headerValue)) {
                throw new InvalidHttpHeadersException('Can only create HTTP headers from string');
            }

            $normalizedHeaderName = self::normalizeHeaderName($headerName);
            $normalizedHeaders[$normalizedHeaderName] = $headerValue;
        }

        return new self($normalizedHeaders);
    }

    private static function normalizeHeaderName(string $headerName): string
    {
        return str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $headerName))));
    }

    public static function fromGlobalRequestHeaders(): HttpHeaders
    {
        $globalRequestHeaders = array_reduce(array_keys($_SERVER), function (array $result, $key) {
            return substr($key, 0, 5) !== 'HTTP_' ?
                $result :
                array_merge($result, [strtolower(str_replace('_', '-', substr($key, 5))) => $_SERVER[$key]]);
        }, []);

        return self::fromArray($globalRequestHeaders);
    }

    public function get(string $headerName): string
    {
        $normalizedHeaderName = self::normalizeHeaderName($headerName);
        if (! $this->has($normalizedHeaderName)) {
            throw new HeaderNotPresentException(sprintf('The header "%s" is not present.', $headerName));
        }

        return $this->headers[$normalizedHeaderName];
    }

    /**
     * @return string[]
     */
    public function getAll(): array
    {
        return $this->headers;
    }

    public function has(string $headerName): bool
    {
        return array_key_exists(self::normalizeHeaderName($headerName), $this->headers);
    }
}
