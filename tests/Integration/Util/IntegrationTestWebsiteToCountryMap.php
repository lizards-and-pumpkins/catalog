<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Website\WebsiteToCountryMap;

class IntegrationTestWebsiteToCountryMap implements WebsiteToCountryMap
{
    /**
     * @param string $websiteCode
     * @return string
     */
    public function getCountry($websiteCode)
    {
        return 'DE';
    }

    /**
     * @return string
     */
    public function getDefaultCountry()
    {
        return 'DE';
    }
}
