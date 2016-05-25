<?php

namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\Locale\ContextLocale;
use LizardsAndPumpkins\Context\Website\Website;

class IntegrationTestContextSource extends ContextSource
{
    /**
     * @return mixed[]
     */
    protected function getContextMatrix()
    {
        return [
            [Website::CONTEXT_CODE => 'ru', ContextLocale::CODE => 'de_DE'],
            [Website::CONTEXT_CODE => 'ru', ContextLocale::CODE => 'en_US'],
            [Website::CONTEXT_CODE => 'cy', ContextLocale::CODE => 'de_DE'],
            [Website::CONTEXT_CODE => 'cy', ContextLocale::CODE => 'en_US'],
            [Website::CONTEXT_CODE => 'fr', ContextLocale::CODE => 'fr_FR'],
        ];
    }
}
