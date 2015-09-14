<?php


namespace LizardsAndPumpkins\Context;

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
