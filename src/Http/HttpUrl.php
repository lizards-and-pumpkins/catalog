<?php

namespace LizardsAndPumpkins\Http;

use League\Url\UrlImmutable;
use League\Url\AbstractUrl;
use LizardsAndPumpkins\Http\Exception\UnknownProtocolException;

class HttpUrl
{
    /**
     * @var \League\Url\AbstractUrl
     */
    private $url;

    protected function __construct(AbstractUrl $url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->url;
    }

    /**
     * @param string $urlString
     * @return HttpUrl
     */
    public static function fromString($urlString)
    {
        try {
            $url = UrlImmutable::createFromUrl($urlString);
        } catch (\RuntimeException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }

        return self::createHttpUrlBasedOnSchema($url);
    }

    /**
     * @return bool
     */
    public function isProtocolEncrypted()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        /** @var \League\Url\Components\Path $path */
        $path = $this->url->getPath();

        return $path->getUriComponent();
    }

    /**
     * @return string
     */
    public function getPathRelativeToWebFront()
    {
        /** @var \League\Url\Components\Path $path */
        $path = $this->url->getPath();
        $path->remove($this->getDirectoryPathRelativeToDocumentRoot());

        return ltrim($path->getUriComponent(), '/');
    }

    /**
     * @param \League\Url\AbstractUrl $url
     * @return HttpUrl
     */
    private static function createHttpUrlBasedOnSchema(AbstractUrl $url)
    {
        switch ($url->getScheme()) {
            case 'https':
                return new HttpsUrl($url);
            case 'http':
                return new HttpUrl($url);
            default:
                throw new UnknownProtocolException(sprintf('Protocol can not be handled "%s"', $url->getScheme()));
        }
    }

    /**
     * @return string
     */
    private function getDirectoryPathRelativeToDocumentRoot()
    {
        return preg_replace('#/[^/]*$#', '', $_SERVER['SCRIPT_NAME']);
    }

    /**
     * @param string $parameterName
     * @return string|null
     */
    public function getQueryParameter($parameterName)
    {
        $requestQuery = $this->url->getQuery();

        if (!isset($requestQuery[$parameterName])) {
            return null;
        }

        return $requestQuery[$parameterName];
    }

    /**
     * @return bool
     */
    public function hasQueryParameters()
    {
        $requestQuery = $this->url->getQuery();
        return count($requestQuery->toArray()) > 0;
    }
}
