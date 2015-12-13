<?php


namespace LizardsAndPumpkins\Website;

use LizardsAndPumpkins\Country\Country;

interface WebsiteToCountryMap
{
    /**
     * @param Website $website
     * @return Country
     */
    public function getCountry(Website $website);

    /**
     * @return Country
     */
    public function getDefaultCountry();
}
