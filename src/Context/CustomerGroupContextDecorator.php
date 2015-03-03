<?php


namespace Brera\Context;

class CustomerGroupContextDecorator extends ContextDecorator
{
    private $code = 'customer_group';
    
    /**
     * @return string
     */
    protected function getCode()
    {
        return $this->code;
    }
}
