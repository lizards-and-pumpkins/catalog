<?php

namespace Brera\PoC\Http;

abstract class HttpRequest
{
    /**
     * @var HttpUrl
     */
    private $url;

    public function __construct(HttpUrl $url)
    {
        $this->url = $url;
    }

    public static function fromGlobalState()
    {
        throw new \Exception('Need to implement');
    }

    /**
     * @param string $requestMethod
     * @param HttpUrl $url
     * @return HttpRequest
     * @throws UnsupportedRequestMethodException
     */
    public static function fromParameters($requestMethod, HttpUrl $url)
    {
        switch (strtoupper($requestMethod)) {
            case 'GET':
                return new HttpGetRequest($url);
            case 'POST':
                return new HttpPostRequest($url);
            default:
                throw new UnsupportedRequestMethodException(sprintf('Unsupported request method: "%s"', $requestMethod));
        }
    }

    /**
     * @return HttpUrl
     */
    public function getUrl()
    {
        return $this->url;
    }
} 
