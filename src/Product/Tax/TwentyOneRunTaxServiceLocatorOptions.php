<?php

namespace LizardsAndPumpkins\Tax;

use LizardsAndPumpkins\Country\Country;
use LizardsAndPumpkins\Website\Website;

class TwentyOneRunTaxServiceLocatorOptions implements TaxServiceLocatorOptions
{
    /**
     * @var Country
     */
    private $country;

    /**
     * @var Website
     */
    private $website;

    public function __construct(Country $country, Website $website)
    {
        $this->country = $country;
        $this->website = $website;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return Website
     */
    public function getWebsite()
    {
        return $this->website;
    }
}
