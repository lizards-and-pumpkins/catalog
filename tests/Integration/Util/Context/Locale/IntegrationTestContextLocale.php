<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Locale;

use LizardsAndPumpkins\Context\ContextPartBuilder;

class IntegrationTestContextLocale implements ContextPartBuilder
{
    private $defaultLocaleCode = 'fr_FR';

    public function getCode() : string
    {
        return Locale::CONTEXT_CODE;
    }

    /**
     * @param mixed[] $inputDataSet
     * @return string
     */
    public function getValue(array $inputDataSet) : string
    {
        if (isset($inputDataSet[Locale::CONTEXT_CODE])) {
            return (string) $inputDataSet[Locale::CONTEXT_CODE];
        }

        return $this->defaultLocaleCode;
    }
}
