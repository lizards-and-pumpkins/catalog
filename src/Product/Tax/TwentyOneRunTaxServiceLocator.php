<?php

namespace LizardsAndPumpkins\Product\Tax;

use LizardsAndPumpkins\Country\Country;
use LizardsAndPumpkins\Product\Tax\Exception\UnableToLocateTaxServiceException;
use LizardsAndPumpkins\Product\Tax\TaxRates\TwentyOneRunTaxRate;
use LizardsAndPumpkins\Product\Tax\TaxRates\TwentyOneRunTaxRateService19;
use LizardsAndPumpkins\Product\Tax\TaxRates\TwentyOneRunTaxRateService20;
use LizardsAndPumpkins\Product\Tax\TaxRates\TwentyOneRunTaxRateService25;
use LizardsAndPumpkins\Website\Website;

class TwentyOneRunTaxServiceLocator implements TaxServiceLocator
{
    /**
     * @param TaxServiceLocatorOptions $options
     * @return TaxService
     */
    public function get(TaxServiceLocatorOptions $options)
    {
        if ($this->isWebsite21Run_DE_or_FR($options)) {
            if ('19%' === $this->getProductTaxClass($options)) {
                switch ($this->getCountry($options)) {
                    case 'DE':
                        return TwentyOneRunTaxRate::create(19);
                        
                    case 'DK':
                        return TwentyOneRunTaxRate::create(25);
                        
                    case 'AT':
                        return TwentyOneRunTaxRate::create(20);
                    
                    case 'FR':
                        return TwentyOneRunTaxRate::create(20);
                    
                    case 'ES':
                        return TwentyOneRunTaxRate::create(21);
                    
                    case 'FI':
                        return TwentyOneRunTaxRate::create(24);
                    
                    case 'NL':
                        return TwentyOneRunTaxRate::create(21);
                    
                    case 'SE':
                        return TwentyOneRunTaxRate::create(25);
                    
                    case 'LU':
                        return TwentyOneRunTaxRate::create(17);
                    
                    case 'IT':
                        return TwentyOneRunTaxRate::create(21);
                    
                    case 'BE':
                        return TwentyOneRunTaxRate::create(21);
                }
            }
            if ('7%' === $this->getProductTaxClass($options)) {
                switch ($this->getCountry($options)) {
                    case 'DE':
                        return TwentyOneRunTaxRate::create(7);
                    
                    case 'DK':
                        return TwentyOneRunTaxRate::create(25);
                    
                    case 'AT':
                        return TwentyOneRunTaxRate::create(20);
                    
                    case 'FR':
                        return TwentyOneRunTaxRate::create(20);
                    
                    case 'ES':
                        return TwentyOneRunTaxRate::create(21);
                    
                    case 'FI':
                        return TwentyOneRunTaxRate::create(24);
                    
                    case 'NL':
                        return TwentyOneRunTaxRate::create(21);
                    
                    case 'SE':
                        return TwentyOneRunTaxRate::create(25);
                    
                    case 'LU':
                        return TwentyOneRunTaxRate::create(17);
                    
                    case 'IT':
                        return TwentyOneRunTaxRate::create(21);
                    
                    case 'BE':
                        return TwentyOneRunTaxRate::create(21);
                }
            }
        }
        if ('cy' === $this->getWebsite($options)) {
            switch ($this->getProductTaxClass($options)) {
                case '21cycles.com':
                    return TwentyOneRunTaxRate::create(19);
                
                case 'VR 7%':
                    return TwentyOneRunTaxRate::create(7);
            }
        }
        throw $this->createUnableToLocateServiceException($options);
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
     * @return string
     */
    private function getSwitchStatementKey(TaxServiceLocatorOptions $options)
    {
        $website = $this->getWebsite($options);
        $productTaxClass = $this->getProductTaxClass($options);
        $country = $this->getCountry($options);
        return $website . '-' . $productTaxClass . '-' . $country;
    }

    /**
     * @param TaxServiceLocatorOptions $options
     * @return bool
     */
    private function isWebsite21Run_DE_or_FR(TaxServiceLocatorOptions $options)
    {
        return in_array($this->getWebsite($options), ['ru', 'fr']);
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
