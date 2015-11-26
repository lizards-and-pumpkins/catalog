<?php


namespace LizardsAndPumpkins\Context\ContextBuilder;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Http\HttpRequest;

class ContextCountry implements ContextPartBuilder
{
    const CODE = 'country';
    
    const COOKIE_NAME = 'breraTransport';
    
    private $cookieDataKey = 'country';
    
    private $defaultCountry = 'fr';

    /**
     * @param mixed[] $inputDataSet
     * @return string
     */
    public function getValue(array $inputDataSet)
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
        $cookieData = json_decode($request->getCookieValue(self::COOKIE_NAME), true);
        return $cookieData && isset($cookieData[$this->cookieDataKey]) ?
            (string) $cookieData[$this->cookieDataKey] :
            $this->defaultCountry;
    }
}
