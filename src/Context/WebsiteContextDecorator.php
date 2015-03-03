<?php


namespace Brera\Context;

class WebsiteContextDecorator extends ContextDecorator
{
    private $code = 'website';
    
    /**
     * @return string
     */
    protected function getCode()
    {
        return $this->code;
    }
}
