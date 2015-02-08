<?php


namespace Brera\Environment;


class LanguageEnvironmentDecorator extends EnvironmentDecorator
{
    const CODE = 'lang';
    
    /**
     * @return string
     */
    protected function getCode()
    {
        return self::CODE;
    }
}
