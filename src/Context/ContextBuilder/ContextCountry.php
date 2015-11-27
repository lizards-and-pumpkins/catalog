<?php


namespace LizardsAndPumpkins\Context\ContextBuilder;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Http\HttpRequest;

class ContextCountry implements ContextPartBuilder
{
    const CODE = 'country';
    
    const COOKIE_NAME = 'breraTransport';
    
    private $cookieDataKey = 'country';
    
    private $defaultCountry = 'de';

    /**
     * @param mixed[] $inputDataSet
     * @param string[] $otherContextParts
     * @return string
     */
    public function getValue(array $inputDataSet, array $otherContextParts)
    {
        if (isset($inputDataSet[self::CODE])) {
            return (string) $inputDataSet[self::CODE];
        }
        if (isset($inputDataSet[ContextBuilder::REQUEST])) {
            return $this->getCountryFromRequest($inputDataSet[ContextBuilder::REQUEST]);
        }
        return $this->defaultCountry;
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
            $this->defaultCountry;
    }
}
