<?php


namespace LizardsAndPumpkins;

interface WebsiteToCountryMap
{
    /**
     * @param string $websiteCode
     * @return string
     */
    public function getCountry($websiteCode);
}
