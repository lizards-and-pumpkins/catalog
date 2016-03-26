<?php

namespace LizardsAndPumpkins\Import\Tax;

use LizardsAndPumpkins\Context\Country\Country;
use LizardsAndPumpkins\Import\Tax\UnableToLocateTaxServiceException;
use LizardsAndPumpkins\Import\Tax\TwentyOneRunTaxRate;
use LizardsAndPumpkins\Context\Website\Website;

class TwentyOneRunTaxServiceLocator implements TaxServiceLocator
{
    private static $rateTable = [
        // [websites], [tax rates],   country, rate
        [['ru', 'fr'], ['19%'],          'DE', 19],
        [['ru', 'fr'], ['7%'],           'DE',  7],
        [['ru', 'fr'], ['19%', '7%'],    'DK', 25],
        [['ru', 'fr'], ['19%', '7%'],    'AT', 20],
        [['ru', 'fr'], ['19%', '7%'],    'FR', 20],
        [['ru', 'fr'], ['19%', '7%'],    'ES', 21],
        [['ru', 'fr'], ['19%', '7%'],    'FI', 24],
        [['ru', 'fr'], ['19%', '7%'],    'NL', 21],
        [['ru', 'fr'], ['19%', '7%'],    'SE', 25],
        [['ru', 'fr'], ['19%', '7%'],    'LU', 17],
        [['ru', 'fr'], ['19%', '7%'],    'IT', 21],
        [['ru', 'fr'], ['19%', '7%'],    'BE', 21],
        [['cy'],       ['21cycles.com'], 'DE', 19],
        [['cy'],       ['VR 7%'],        'DE',  7],
    ];
    
    private static $websiteIdx = 0;
    private static $taxClassIdx = 1;
    private static $countryIdx = 2;
    private static $rateIdx = 3;

    /**
     * @var Website
     */
    private $website;

    /**
     * @var Country
     */
    private $country;

    /**
     * @var ProductTaxClass
     */
    private $taxClass;

    /**
     * @param mixed[] $options
     * @return TaxService
     */
    public function get(array $options)
    {
        $this->website = $this->getWebsiteFromOptions($options);
        $this->taxClass = $this->getProductTaxClassFromOptions($options);
        $this->country = $this->getCountryFromOptions($options);
        
        return $this->findRule();
    }

    /**
     * @return TwentyOneRunTaxRate
     */
    private function findRule()
    {
        foreach (self::$rateTable as $rule) {
            if ($this->isMatchingRule($rule)) {
                return TwentyOneRunTaxRate::fromInt($rule[self::$rateIdx]);
            }
        }
        throw $this->createUnableToLocateServiceException();
    }

    /**
     * @param mixed[] $rule
     * @return bool
     */
    private function isMatchingRule(array $rule)
    {
        return
            in_array((string) $this->website, $rule[self::$websiteIdx]) &&
            in_array((string) $this->taxClass, $rule[self::$taxClassIdx]) &&
            (string) $this->country === $rule[self::$countryIdx];
    }

    /**
     * @param mixed[] $options
     * @return Country
     */
    private function getCountryFromOptions(array $options)
    {
        return Country::from2CharIso3166($options[self::OPTION_COUNTRY]);
    }

    /**
     * @param mixed[] $options
     * @return ProductTaxClass
     */
    private function getProductTaxClassFromOptions(array $options)
    {
        return ProductTaxClass::fromString($options[self::OPTION_PRODUCT_TAX_CLASS]);
    }

    /**
     * @param mixed[] $options
     * @return Website
     */
    private function getWebsiteFromOptions(array $options)
    {
        return Website::fromString($options[self::OPTION_WEBSITE]);
    }

    /**
     * @return UnableToLocateTaxServiceException
     */
    private function createUnableToLocateServiceException()
    {
        $message = sprintf(
            'Unable to locate a tax service for website "%s", product tax class "%s" and country "%s"',
            $this->website,
            $this->taxClass,
            $this->country
        );
        return new UnableToLocateTaxServiceException($message);
    }
}
