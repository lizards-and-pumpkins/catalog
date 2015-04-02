<?php

namespace Brera\Context;

class StubValidTestContextDecorator extends ContextDecorator
{
    /**
     * @return string
     */
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
