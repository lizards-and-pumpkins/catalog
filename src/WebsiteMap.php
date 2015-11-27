<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\InvalidWebsiteMapConfigRecordException;
use LizardsAndPumpkins\Exception\UnknownWebsiteHostException;

class WebsiteMap
{
    const CONFIG_KEY = 'website_map';
    const RECORD_SEPARATOR = '|';

    /**
     * @var string[]
     */
    private $hostToWebsiteMap;

    /**
     * @param string[] $hostToWebsiteMap
     */
    private function __construct(array $hostToWebsiteMap)
    {
        $this->hostToWebsiteMap = $hostToWebsiteMap;
    }

    /**
     * @param string[] $hostToWebsiteMap
     * @return WebsiteMap
     */
    public static function fromArray(array $hostToWebsiteMap)
    {
        return new static($hostToWebsiteMap);
    }

    /**
     * @param ConfigReader $configReader
     * @return WebsiteMap
     */
    public static function fromConfig(ConfigReader $configReader)
    {
        $hostToWebsiteMap = $configReader->get(self::CONFIG_KEY) ?
            self::buildArrayMapFromString($configReader->get(self::CONFIG_KEY)) :
            [];
        
        return new static($hostToWebsiteMap);
    }

    /**
     * @param string $configValue
     * @return mixed
     */
    private static function buildArrayMapFromString($configValue)
    {
        $pairs = array_map('self::splitConfigRecord', explode(self::RECORD_SEPARATOR, $configValue));
        
        return self::flatten($pairs);
    }

    /**
     * @param string $mapping
     * @return string[]
     */
    private static function splitConfigRecord($mapping)
    {
        if (! preg_match('/^([^=]+)=(.+)/', $mapping, $matches)) {
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
     * @param string $host
     * @return string
     */
    public function getWebsiteCodeByHost($host)
    {
        if (!isset($this->hostToWebsiteMap[$host])) {
            throw new UnknownWebsiteHostException(sprintf('No website code found for host "%s"', $host));
        }
        return $this->hostToWebsiteMap[$host];
    }
}
