<?php

namespace LizardsAndPumpkins\Context\Website;

use LizardsAndPumpkins\Context\Country\Country;

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
