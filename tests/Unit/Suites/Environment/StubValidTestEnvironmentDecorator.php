<?php

namespace Brera\Environment;

class StubValidTestEnvironmentDecorator extends EnvironmentDecorator
{
    protected function getValueFromEnvironment()
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
