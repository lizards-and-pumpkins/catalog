<?php

namespace LizardsAndPumpkins\Product\Tax;

use LizardsAndPumpkins\Country\Country;
use LizardsAndPumpkins\Product\Tax\Exception\UnableToLocateTaxServiceException;
use LizardsAndPumpkins\Product\Tax\TaxRates\TwentyOneRunTaxRate;
use LizardsAndPumpkins\Website\Website;

class TwentyOneRunTaxServiceLocator implements TaxServiceLocator
{
    private static $rateTable = [
        [['ru', 'fr'], ['19%'], 'DE', 19],
        [['ru', 'fr'], ['7%'], 'DE', 7],
        [['ru', 'fr'], ['19%', '7%'], 'DK', 25],
        [['ru', 'fr'], ['19%', '7%'], 'AT', 20],
        [['ru', 'fr'], ['19%', '7%'], 'FR', 20],
        [['ru', 'fr'], ['19%', '7%'], 'ES', 21],
        [['ru', 'fr'], ['19%', '7%'], 'FI', 24],
        [['ru', 'fr'], ['19%', '7%'], 'NL', 21],
        [['ru', 'fr'], ['19%', '7%'], 'SE', 25],
        [['ru', 'fr'], ['19%', '7%'], 'LU', 17],
        [['ru', 'fr'], ['19%', '7%'], 'IT', 21],
        [['ru', 'fr'], ['19%', '7%'], 'BE', 21],
        [['cy'], ['21cycles.com'], 'DE', 19],
        [['cy'], ['VR 7%'], 'DE', 7],
    ];

    /**
     * @param TaxServiceLocatorOptions $options
     * @return TaxService
     */
    public function get(TaxServiceLocatorOptions $options)
    {
        foreach (self::$rateTable as $rule) {
            if ($this->isMatchingRule($rule, $options)) {
                return TwentyOneRunTaxRate::create($rule[$rateIdx = 3]);
            }
        }
        throw $this->createUnableToLocateServiceException($options);
    }

    /**
     * @param mixed[] $rule
     * @param TaxServiceLocatorOptions $options
     * @return bool
     */
    private function isMatchingRule(array $rule, TaxServiceLocatorOptions $options)
    {
        $websiteIdx = 0;
        $taxClassIdx = 1;
        $countryIdx = 2;
        return
            in_array($this->getWebsite($options), $rule[$websiteIdx]) &&
            in_array($this->getProductTaxClass($options), $rule[$taxClassIdx]) &&
            $this->getCountry($options) === $rule[$countryIdx];
    }
    
    /**
     * @param TwentyOneRunTaxServiceLocatorOptions $options
     * @return Country
     */
    private function getCountry(TwentyOneRunTaxServiceLocatorOptions $options)
    {
        return (string) $options->getCountry();
    }

    /**
     * @param TwentyOneRunTaxServiceLocatorOptions $options
     * @return ProductTaxClass
     */
    private function getProductTaxClass(TwentyOneRunTaxServiceLocatorOptions $options)
    {
        return (string) $options->getProductTaxClass();
    }

    /**
     * @param TwentyOneRunTaxServiceLocatorOptions $options
     * @return Website
     */
    private function getWebsite(TwentyOneRunTaxServiceLocatorOptions $options)
    {
        return (string) $options->getWebsite();
    }

    /**
     * @param TaxServiceLocatorOptions $options
     * @return UnableToLocateTaxServiceException
     */
    private function createUnableToLocateServiceException(TaxServiceLocatorOptions $options)
    {
        $message = sprintf(
            'Unable to locate a tax service for website "%s", product tax class "%s" and country "%s"',
            $this->getWebsite($options),
            $this->getProductTaxClass($options),
            $this->getCountry($options)
        );
        return new UnableToLocateTaxServiceException($message);
    }
}
