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

        self::validateProtocol($url);

        return new self($url);
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
    public function getPathWithoutWebsitePrefix()
    {
        /** @var \League\Url\Components\Path $path */
        $path = $this->url->getPath();
        $path->remove($this->getAppEntryPointPath());

        return ltrim($path->getUriComponent(), '/');
    }

    /**
     * @return string
     */
    public function getPathWithWebsitePrefix()
    {
        $pathToRemove = preg_replace('#/[^/]*$#', '', $this->getAppEntryPointPath());
        
        /** @var \League\Url\Components\Path $path */
        $path = $this->url->getPath();
        $path->remove($pathToRemove);
        
        return ltrim($path->getUriComponent(), '/');
    }

    /**
     * @return string
     */
    private function getAppEntryPointPath()
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
        /** @var \League\Url\Components\QueryInterface $requestQuery */
        $requestQuery = $this->url->getQuery();
        return count($requestQuery->toArray()) > 0;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return (string) $this->url->getHost();
    }

    private static function validateProtocol(AbstractUrl $url)
    {
        if (! in_array($url->getScheme(), ['http', 'https', ''])) {
            throw new UnknownProtocolException(sprintf('Protocol can not be handled "%s"', $url->getScheme()));
        }
    }
}
