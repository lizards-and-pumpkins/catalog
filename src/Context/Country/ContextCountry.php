<?php

namespace LizardsAndPumpkins\Context\Country;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextPartBuilder;
use LizardsAndPumpkins\Context\Website\ContextWebsite;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Context\Website\WebsiteToCountryMap;

class ContextCountry implements ContextPartBuilder
{
    const CODE = 'country';
    
    const COOKIE_NAME = 'lizardsAndPumpkinsTransport';
    
    private $cookieDataKey = 'country';
    
    /**
     * @var WebsiteToCountryMap
     */
    private $websiteToCountryMap;

    public function __construct(WebsiteToCountryMap $websiteToCountryMap)
    {
        $this->websiteToCountryMap = $websiteToCountryMap;
    }
    
    /**
     * @param mixed[] $inputDataSet
     * @return string|null
     */
    public function getValue(array $inputDataSet)
    {
        if (isset($inputDataSet[self::CODE])) {
            return (string) $inputDataSet[self::CODE];
        }
        if (isset($inputDataSet[ContextBuilder::REQUEST])) {
            return $this->getCountryFromRequest($inputDataSet[ContextBuilder::REQUEST]);
        }
        return null;
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
    private function getCountryFromRequest(HttpRequest $request)
    {
        $cookieData = $request->hasCookie(self::COOKIE_NAME) ?
            json_decode($request->getCookieValue(self::COOKIE_NAME), true) :
            false;
        return $cookieData && isset($cookieData[$this->cookieDataKey]) ?
            (string) $cookieData[$this->cookieDataKey] :
            $this->websiteToCountryMap->getDefaultCountry();
    }

    /**
     * @param string[] $otherContextParts
     * @return string
     */
    private function getWebsiteCode(array $otherContextParts)
    {
        return isset($otherContextParts[ContextWebsite::CODE]) ?
            $otherContextParts[ContextWebsite::CODE] :
            '';
    }
}
