<?php

namespace LizardsAndPumpkins\Context\Stub;

use LizardsAndPumpkins\Context\ContextPartBuilder;

class FromInputCopyingTestContextPartBuilder implements ContextPartBuilder
{
    /**
     * @var string
     */
    private $code;

    /**
     * @param string $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * @param mixed[] $inputDataSet
     * @return string|null
     */
    public function getValue(array $inputDataSet)
    {
        return isset($inputDataSet[$this->code]) ?
            $inputDataSet[$this->code] :
            null;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
