<?php


namespace Brera\Context;

class LocaleContextDecorator extends ContextDecorator
{
    const CODE = 'locale';
    
    /**
     * @return string
     */
    protected function getCode()
    {
        return self::CODE;
    }
}
