<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Context\LocaleContextDecorator;
use LizardsAndPumpkins\Context\WebsiteContextDecorator;

class SampleContextSource extends ContextSource
{
    /**
     * @return mixed[]
     */
    protected function getContextMatrix()
    {
        return [
            [WebsiteContextDecorator::CODE => 'ru', LocaleContextDecorator::CODE => 'de_DE'],
            [WebsiteContextDecorator::CODE => 'ru', LocaleContextDecorator::CODE => 'en_US'],
            [WebsiteContextDecorator::CODE => 'cy', LocaleContextDecorator::CODE => 'de_DE'],
            [WebsiteContextDecorator::CODE => 'cy', LocaleContextDecorator::CODE => 'en_US'],
        ];
    }
}
