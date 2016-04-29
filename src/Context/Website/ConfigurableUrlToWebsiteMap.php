<?php

namespace LizardsAndPumpkins\Context\Website;

use LizardsAndPumpkins\Context\Website\Exception\InvalidWebsiteMapConfigRecordException;
use LizardsAndPumpkins\Context\Website\Exception\UnknownWebsiteHostException;
use LizardsAndPumpkins\Util\Config\ConfigReader;

class ConfigurableUrlToWebsiteMap implements UrlToWebsiteMap
{
    const CONFIG_KEY = 'website_map';
    const RECORD_SEPARATOR = ',';

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
    private static function createWebsites(array $map)
    {
        return array_reduce(array_keys($map), function (array $carry, $url) use ($map) {
            return array_merge($carry, [$url => Website::fromString($map[$url])]);
        }, []);
    }

    /**
     * @param string $configValue
     * @return mixed
     */
    private static function buildArrayMapFromString($configValue)
    {
        $pairs = array_map([self::class, 'splitConfigRecord'], explode(self::RECORD_SEPARATOR, $configValue));

        return self::flatten($pairs);
    }

    /**
     * @param string $mapping
     * @return string[]
     */
    private static function splitConfigRecord($mapping)
    {
        if (!preg_match('/^([^=]+)=(.+)/', $mapping, $matches)) {
            $message = sprintf('Unable to parse the website to code mapping record "%s"', $mapping);
            throw new InvalidWebsiteMapConfigRecordException($message);
        }

        return [$matches[1] => $matches[2]];
    }

    /**
     * @param array[] $array
     * @return string[]
     */
    private static function flatten($array)
    {
        return array_reduce($array, function (array $carry, array $pair) {
            return array_merge($carry, $pair);
        }, []);
    }

    /**
     * @param string $url
     * @return Website
     */
    public function getWebsiteCodeByUrl($url)
    {
        foreach ($this->urlToWebsiteMap as $urlPattern => $website) {
            if (preg_match('#' . $urlPattern . '#', $url)) {
                return $website;
            }
        }

        throw new UnknownWebsiteHostException(sprintf('No website code found for url "%s"', $url));
    }
}
