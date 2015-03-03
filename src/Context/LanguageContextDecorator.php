<?php


namespace Brera\Context;

class LanguageContextDecorator extends ContextDecorator
{
    private $code = 'language';
    
    /**
     * @return string
     */
    protected function getCode()
    {
        return $this->code;
    }
}
