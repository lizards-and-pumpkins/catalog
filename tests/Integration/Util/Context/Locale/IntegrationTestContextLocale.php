<?php

namespace LizardsAndPumpkins\Context\Locale;

use LizardsAndPumpkins\Context\ContextPartBuilder;

class IntegrationTestContextLocale implements ContextPartBuilder
{
    private $defaultLocaleCode = 'fr_FR';

    /**
     * @return string
     */
    public function getCode()
    {
        return Locale::CONTEXT_CODE;
    }

    /**
     * @param mixed[] $inputDataSet
     * @return string
     */
    public function getValue(array $inputDataSet)
    {
        if (isset($inputDataSet[Locale::CONTEXT_CODE])) {
            return (string) $inputDataSet[Locale::CONTEXT_CODE];
        }

        return $this->defaultLocaleCode;
    }
}
