<?php

namespace LizardsAndPumpkins\Context;

class StubValidTestContextDecorator extends ContextDecorator
{
    /**
     * @return string
     */
    protected function getCode()
    {
        return 'stub_valid_test';
    }
}
