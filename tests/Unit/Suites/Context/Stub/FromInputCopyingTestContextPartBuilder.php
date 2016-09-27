<?php

namespace LizardsAndPumpkins\Context\Stub;

use LizardsAndPumpkins\Context\ContextPartBuilder;

class FromInputCopyingTestContextPartBuilder implements ContextPartBuilder
{
    /**
     * @var string
     */
    private $code;

    public function __construct(string $code)
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

    public function getCode() : string
    {
        return $this->code;
    }
}
