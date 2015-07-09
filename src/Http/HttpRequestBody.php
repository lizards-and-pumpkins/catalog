<?php


namespace Brera\Http;

class HttpRequestBody
{
    /**
     * @var string
     */
    private $requestBody;

    /**
     * @param string $requestBody
     */
    private function __construct($requestBody)
    {
        $this->requestBody = $requestBody;
    }
    
    /**
     * @param string $requestBody
     * @return HttpRequestBody
     */
    public static function fromString($requestBody)
    {
        if (! is_string($requestBody)) {
            throw new InvalidHttpRequestBodyException(
                sprintf('The request body has to be of type string, got "%s"', gettype($requestBody))
            );
        }
        return new self($requestBody);
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->requestBody;
    }
}
