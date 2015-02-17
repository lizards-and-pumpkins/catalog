<?php

namespace Brera\Context;

class StubValidTestContextDecorator extends ContextDecorator
{
    protected function getValueFromContext()
    {
        return '';
    }

    /**
     * @return string
     */
    protected function getCode()
    {
        return 'valid_test_stub';
    }
}
