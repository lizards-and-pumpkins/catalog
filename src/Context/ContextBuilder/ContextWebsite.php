<?php


namespace LizardsAndPumpkins\Context\ContextBuilder;

use LizardsAndPumpkins\Context\ContextBuilder\Exception\UnableToDetermineContextWebsiteException;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\WebsiteMap;

class ContextWebsite implements ContextPartBuilder
{
    const CODE = 'website';
    
    /**
     * @var WebsiteMap
     */
    private $websiteMap;

    public function __construct(WebsiteMap $websiteMap)
    {
        $this->websiteMap = $websiteMap;
    }
    
    /**
     * @param mixed[] $inputDataSet
     * @return string
     */
    public function getValue(array $inputDataSet)
    {
        if (isset($inputDataSet[self::CODE])) {
            return (string) $inputDataSet[self::CODE];
        }
        if (isset($inputDataSet['request'])) {
            return (string) $this->getHostFromRequest($inputDataSet['request']);
        }
        $message = 'Unable to determine context website because neither the ' .
            'website nor the request are set in the input array.';
        throw new UnableToDetermineContextWebsiteException($message);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return self::CODE;
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getHostFromRequest(HttpRequest $request)
    {
        return $this->websiteMap->getCodeByHost($request->getHost());
    }
}
