<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Website;

use LizardsAndPumpkins\Context\Website\Exception\InvalidWebsiteMapConfigRecordException;
use LizardsAndPumpkins\Context\Website\Exception\UnknownWebsiteUrlException;
use LizardsAndPumpkins\Util\Config\ConfigReader;

class ConfigurableUrlToWebsiteMap implements UrlToWebsiteMap
{
    const CONFIG_KEY = 'base_url_to_website_map';
    const RECORD_SEPARATOR = '|';

    /**
     * @var string[]
     */
    private $urlToWebsiteMap;

    /**
     * @param Website[] $urlToWebsiteMap
     */
    private function __construct(array $urlToWebsiteMap)
    {
        $this->urlToWebsiteMap = $urlToWebsiteMap;
    }

    /**
     * @param ConfigReader $configReader
     * @return ConfigurableUrlToWebsiteMap
     */
    public static function fromConfig(ConfigReader $configReader)
    {
        $urlToWebsiteMap = $configReader->get(self::CONFIG_KEY) ?
            self::buildArrayMapFromString($configReader->get(self::CONFIG_KEY)) :
            [];

        return new static(self::createWebsites($urlToWebsiteMap));
    }

    /**
     * @param string[] $map
     * @return Website[]
     */
    private static function createWebsites(array $map): array
    {
        return array_reduce(array_keys($map), function (array $carry, $url) use ($map) {
            return array_merge($carry, [$url => Website::fromString($map[$url])]);
        }, []);
    }

    /**
     * @param string $configValue
     * @return mixed
     */
    private static function buildArrayMapFromString(string $configValue)
    {
        $pairs = array_map([self::class, 'splitConfigRecord'], explode(self::RECORD_SEPARATOR, $configValue));

        return array_merge(...$pairs);
    }

    /**
     * @param string $mapping
     * @return string[]
     */
    private static function splitConfigRecord(string $mapping): array
    {
        if (! preg_match('/^([^=]+)=(.+)/', $mapping, $matches)) {
            $message = sprintf('Unable to parse the website to code mapping record "%s"', $mapping);
            throw new InvalidWebsiteMapConfigRecordException($message);
        }

        return [$matches[1] => $matches[2]];
    }

    /**
     * @param string $url
     * @return string[]
     */
    private function getWebsiteUrlPrefixAndCodeByUrl(string $url): array
    {
        foreach ($this->urlToWebsiteMap as $urlPrefix => $website) {
            if (stripos($url, $urlPrefix) === 0) {
                return [$urlPrefix, $website];
            }
        }

        throw new UnknownWebsiteUrlException(sprintf('No website found for url "%s"', $url));
    }

    public function getWebsiteCodeByUrl(string $url): Website
    {
        list($urlPrefix, $website) = $this->getWebsiteUrlPrefixAndCodeByUrl($url);

        return $website;
    }

    public function getRequestPathWithoutWebsitePrefix(string $url): string
    {
        list($urlPrefix, $website) = $this->getWebsiteUrlPrefixAndCodeByUrl($url);

        $relevantUrlParts = $this->removeQueryAndAnchor($url);

        return trim(substr($relevantUrlParts, strlen($urlPrefix)), '/');
    }

    private function removeQueryAndAnchor(string $url): string
    {
        $parts = parse_url($url);

        return (isset($parts['scheme']) ? $parts['scheme'] . '://' : '') .
               ($parts['host'] ?? '') .
               (isset($parts['port']) ? ':' . $parts['port'] : '') .
               (isset($parts['path']) ? $parts['path'] : '/');
    }
}
