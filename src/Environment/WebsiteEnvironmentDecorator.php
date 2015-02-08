<?php


namespace Brera\Environment;


class WebsiteEnvironmentDecorator extends EnvironmentDecorator
{
    const CODE = 'website';
    
    /**
     * @return string
     */
    protected function getCode()
    {
        return self::CODE;
    }
}
