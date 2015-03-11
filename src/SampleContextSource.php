<?php

namespace Brera;

use Brera\Context\ContextSource;
use Brera\Context\LanguageContextDecorator;
use Brera\Context\WebsiteContextDecorator;

class SampleContextSource extends ContextSource
{
    /**
     * @return mixed[]
     */
    protected function getContextMatrix()
    {
        return [
            [WebsiteContextDecorator::CODE => 'ru', LanguageContextDecorator::CODE => 'de_DE'],
            [WebsiteContextDecorator::CODE => 'ru', LanguageContextDecorator::CODE => 'en_US'],
            [WebsiteContextDecorator::CODE => 'cy', LanguageContextDecorator::CODE => 'de_DE'],
            [WebsiteContextDecorator::CODE => 'cy', LanguageContextDecorator::CODE => 'en_US'],
        ];
    }
}
