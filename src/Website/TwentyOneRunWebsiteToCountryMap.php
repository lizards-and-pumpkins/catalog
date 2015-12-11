<?php


namespace LizardsAndPumpkins\Website;

use LizardsAndPumpkins\Country\Country;

class TwentyOneRunWebsiteToCountryMap implements WebsiteToCountryMap
{
    private $defaultCountry = 'DE';

    private $map = [
        'ru' => 'DE',
        'fr' => 'FR',
    ];

    /**
     * @param Website $website
     * @return Country
     */
    public function getCountry(Website $website)
    {
        return Country::from2CharIso3166($this->getCountryFromMap((string) $website));
    }
    
    /**
     * @return Country
     */
    public function getDefaultCountry()
    {
        return Country::from2CharIso3166($this->defaultCountry);
    }

    /**
     * @param string $mapKey
     * @return string
     */
    private function getCountryFromMap($mapKey)
    {
        return isset($this->map[$mapKey]) ?
            $this->map[$mapKey] :
            $this->defaultCountry;
    }
}
