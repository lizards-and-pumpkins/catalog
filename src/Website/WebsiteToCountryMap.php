<?php


namespace LizardsAndPumpkins\Website;

interface WebsiteToCountryMap
{
    /**
     * @param string $websiteCode
     * @return string
     */
    public function getCountry($websiteCode);

    /**
     * @return string
     */
    public function getDefaultCountry();
}
