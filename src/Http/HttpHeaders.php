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
     * @return static
     */
    public static function fromArray(array $headers)
    {
        return new static($headers);
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
