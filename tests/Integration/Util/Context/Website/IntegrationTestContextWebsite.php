<?php

namespace LizardsAndPumpkins\Context\Website;

use LizardsAndPumpkins\Context\ContextPartBuilder;

class IntegrationTestContextWebsite implements ContextPartBuilder
{
    private $defaultWebsiteCode = 'fr';

    /**
     * @param mixed[] $inputDataSet
     * @return string
     */
    public function getValue(array $inputDataSet)
    {
        if (isset($inputDataSet[Website::CONTEXT_CODE])) {
            return (string) $inputDataSet[Website::CONTEXT_CODE];
        }
        
        return $this->defaultWebsiteCode;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return Website::CONTEXT_CODE;
    }
}
