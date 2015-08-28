<?php


namespace Brera\Context;

class WebsiteContextDecorator extends ContextDecorator
{
    const CODE = 'website';

    /**
     * @return string
     */
    protected function getCode()
    {
        return self::CODE;
    }

    /**
     * @return string
     */
    protected function getValueFromContext()
    {
        return parent::getValueFromContext();
    }
}
