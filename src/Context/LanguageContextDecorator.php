<?php


namespace Brera\Context;

class LanguageContextDecorator extends ContextDecorator
{
    const CODE = 'language';
    
    /**
     * @return string
     */
    protected function getCode()
    {
        return self::CODE;
    }
}
