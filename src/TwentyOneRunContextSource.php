<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
use LizardsAndPumpkins\Context\ContextBuilder\ContextWebsite;
use LizardsAndPumpkins\Context\ContextSource;

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
