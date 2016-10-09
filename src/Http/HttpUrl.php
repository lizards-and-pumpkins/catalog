<?php

declare(strict_types=1);

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

    private function __construct(AbstractUrl $url)
    {
        $this->url = $url;
    }

    public function __toString() : string
    {
        return (string) $this->url;
    }

    public static function fromString(string $urlString) : HttpUrl
    {
        try {
            $url = UrlImmutable::createFromUrl($urlString);
        } catch (\RuntimeException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }

        self::validateProtocol($url);

        return new self($url);
    }

    public function getPath() : string
    {
        /** @var \League\Url\Components\Path $path */
        $path = $this->url->getPath();

        return $path->getUriComponent();
    }

    public function getPathWithoutWebsitePrefix() : string
    {
        /** @var \League\Url\Components\Path $path */
        $path = $this->url->getPath();
        $path->remove($this->getAppEntryPointPath());

        return ltrim($path->getUriComponent(), '/');
    }

    public function getPathWithWebsitePrefix() : string
    {
        $pathToRemove = preg_replace('#/[^/]*$#', '', $this->getAppEntryPointPath());
        
        /** @var \League\Url\Components\Path $path */
        $path = $this->url->getPath();
        $path->remove($pathToRemove);
        
        return ltrim($path->getUriComponent(), '/');
    }

    private function getAppEntryPointPath() : string
    {
        return preg_replace('#/[^/]*$#', '', $_SERVER['SCRIPT_NAME']);
    }

    /**
     * @param string $parameterName
     * @return string|null
     */
    public function getQueryParameter(string $parameterName)
    {
        $requestQuery = $this->url->getQuery();

        if (!isset($requestQuery[$parameterName])) {
            return null;
        }

        return $requestQuery[$parameterName];
    }

    public function hasQueryParameters() : bool
    {
        /** @var \League\Url\Components\QueryInterface $requestQuery */
        $requestQuery = $this->url->getQuery();
        return count($requestQuery->toArray()) > 0;
    }

    public function getHost() : string
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
