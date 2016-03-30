<?php

namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\Locale\ContextLocale;
use LizardsAndPumpkins\Context\Website\ContextWebsite;


class TwentyOneRunContextSource extends ContextSource
{
    /**
     * @return mixed[]
     */
    protected function getContextMatrix()
    {
        return [
            [ContextWebsite::CODE => 'ru', ContextLocale::CODE => 'de_DE'],
            [ContextWebsite::CODE => 'ru', ContextLocale::CODE => 'en_US'],
            [ContextWebsite::CODE => 'cy', ContextLocale::CODE => 'de_DE'],
            [ContextWebsite::CODE => 'cy', ContextLocale::CODE => 'en_US'],
            [ContextWebsite::CODE => 'fr', ContextLocale::CODE => 'fr_FR'],
        ];
    }
}
