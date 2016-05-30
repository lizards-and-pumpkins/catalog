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
    public function getValue(array $inputDataSet)
    {
        if (isset($inputDataSet[Country::CONTEXT_CODE])) {
            return (string) $inputDataSet[Country::CONTEXT_CODE];
        }

        return $this->defaultCountryCode;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return Country::CONTEXT_CODE;
    }
}
