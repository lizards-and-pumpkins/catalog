<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Exception\CookieNotSetException;
use LizardsAndPumpkins\Http\Routing\Exception\UnsupportedRequestMethodException;

abstract class HttpRequest
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD = 'HEAD';

    /**
     * @var HttpUrl
     */
    private $url;

    /**
     * @var HttpHeaders
     */
    private $headers;

    /**
     * @var HttpRequestBody
     */
    private $body;

    final public function __construct(HttpUrl $url, HttpHeaders $headers, HttpRequestBody $body)
    {
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
    }

    public static function fromGlobalState(string $requestBody = '') : HttpRequest
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        $protocol = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) {
            $protocol = 'https';
        }

        $url = HttpUrl::fromString($protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $headers = HttpHeaders::fromGlobalRequestHeaders();
        $body = new HttpRequestBody($requestBody);

        return self::fromParameters($requestMethod, $url, $headers, $body);
    }

    public static function fromParameters(
        string $requestMethod,
        HttpUrl $url,
        HttpHeaders $headers,
        HttpRequestBody $body
    ) : HttpRequest {
        switch (strtoupper($requestMethod)) {
            case self::METHOD_GET:
            case self::METHOD_HEAD:
                return new HttpGetRequest($url, $headers, $body);
            case self::METHOD_POST:
                return new HttpPostRequest($url, $headers, $body);
            case self::METHOD_PUT:
                return new HttpPutRequest($url, $headers, $body);
            default:
                throw new UnsupportedRequestMethodException(
                    sprintf('Unsupported request method: "%s"', $requestMethod)
                );
        }
    }

    public function getUrl() : HttpUrl
    {
        return $this->url;
    }

    public function getPathWithoutWebsitePrefix() : string
    {
        return $this->getUrl()->getPathWithoutWebsitePrefix();
    }

    public function getHeader(string $headerName) : string
    {
        return $this->headers->get($headerName);
    }

    public function getRawBody() : string
    {
        return $this->body->toString();
    }

    abstract public function getMethod() : string;

    /**
     * @param string $parameterName
     * @return null|string
     */
    public function getQueryParameter(string $parameterName)
    {
        // TODO: Once league/url is gone refactor to hasQueryParameter and exception
        return $this->url->getQueryParameter($parameterName);
    }

    public function hasQueryParameters() : bool
    {
        return $this->url->hasQueryParameters();
    }

    /**
     * @return string[]
     */
    public function getCookies() : array
    {
        return $_COOKIE;
    }

    public function hasCookie(string $cookieName) : bool
    {
        return isset($_COOKIE[$cookieName]);
    }

    public function getCookieValue(string $cookieName) : string
    {
        if (!$this->hasCookie($cookieName)) {
            throw new CookieNotSetException(sprintf('Cookie with "%s" name is not set.', $cookieName));
        }

        return $_COOKIE[$cookieName];
    }

    public function getHost() : string
    {
        return $this->url->getHost();
    }
}
