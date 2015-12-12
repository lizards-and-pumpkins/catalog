<?php

namespace LizardsAndPumpkins\Product\Tax;

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

    /**
     * @var ProductTaxClass
     */
    private $productTaxClass;

    public function __construct(Website $website, ProductTaxClass $productTaxClass, Country $country)
    {
        $this->website = $website;
        $this->productTaxClass = $productTaxClass;
        $this->country = $country;
    }

    /**
     * @param string $websiteCode
     * @param string $productTaxClass
     * @param string $countryCode
     */
    public static function fromStrings($websiteCode, $productTaxClass, $countryCode)
    {
        return new self(
            Website::fromString($websiteCode),
            ProductTaxClass::fromString($productTaxClass),
            Country::from2CharIso3166($countryCode)
        );
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

    /**
     * @return ProductTaxClass
     */
    public function getProductTaxClass()
    {
        return $this->productTaxClass;
    }
}
