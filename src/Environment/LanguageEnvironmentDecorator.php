<?php


namespace Brera\Environment;

class LanguageEnvironmentDecorator extends EnvironmentDecorator
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
