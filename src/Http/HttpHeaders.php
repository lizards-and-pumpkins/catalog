<?php


namespace Brera\Http;

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
    public static function fromArray(array $headers)
    {
        $normalizedHeaders = [];

        foreach ($headers as $headerName => $headerValue) {
            if (!is_string($headerName) || !is_string($headerValue)) {
                throw new InvalidHttpHeadersException('Can only create HTTP headers from string');
            }

            $normalizedHeaderName = self::normalizeHeaderName($headerName);
            $normalizedHeaders[$normalizedHeaderName] = $headerValue;
        }

        return new self($normalizedHeaders);
    }

    /**
     * @param string $headerName
     * @return string
     * @throws HeaderNotPresentException
     */
    public function get($headerName)
    {
        $normalizedHeaderName = $this->normalizeHeaderName($headerName);
        if (! $this->has($normalizedHeaderName)) {
            throw new HeaderNotPresentException(sprintf('The header "%s" is not present.', $headerName));
        }
        return $this->headers[$normalizedHeaderName];
    }

    /**
     * @return string[]
     */
    public function getAll()
    {
        return $this->headers;
    }

    /**
     * @param string $headerName
     * @return bool
     */
    public function has($headerName)
    {
        return array_key_exists($this->normalizeHeaderName($headerName), $this->headers);
    }

    /**
     * @param string $headerName
     * @return string
     */
    private function normalizeHeaderName($headerName)
    {
        return strtolower($headerName);
    }
}
