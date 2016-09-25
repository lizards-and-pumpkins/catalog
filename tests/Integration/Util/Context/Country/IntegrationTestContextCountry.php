<?php

namespace LizardsAndPumpkins\Context\Country;

use LizardsAndPumpkins\Context\ContextPartBuilder;

class IntegrationTestContextCountry implements ContextPartBuilder
{
    private $defaultCountryCode = 'DE';
    
    /**
     * @param mixed[] $inputDataSet
     * @return string
     */
    public function getValue(array $inputDataSet) : string
    {
        if (isset($inputDataSet[Country::CONTEXT_CODE])) {
            return (string) $inputDataSet[Country::CONTEXT_CODE];
        }

        return $this->defaultCountryCode;
    }

    public function getCode() : string
    {
        return Country::CONTEXT_CODE;
    }
}
