<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Country\Country;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\Context\Website\WebsiteToCountryMap;

class IntegrationTestWebsiteToCountryMap implements WebsiteToCountryMap
{
    /**
     * @param Website $website
     * @return Country
     */
    public function getCountry(Website $website)
    {
        return Country::from2CharIso3166('DE');
    }

    /**
     * @return Country
     */
    public function getDefaultCountry()
    {
        return Country::from2CharIso3166('DE');
    }
}
