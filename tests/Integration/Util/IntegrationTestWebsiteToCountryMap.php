<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Country\Country;
use LizardsAndPumpkins\Website\Website;
use LizardsAndPumpkins\Website\WebsiteToCountryMap;

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
