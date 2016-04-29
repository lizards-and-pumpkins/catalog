<?php

namespace LizardsAndPumpkins\Context;

interface ContextPartBuilder
{
    /**
     * @param mixed[] $inputDataSet
     * @return string|null
     */
    public function getValue(array $inputDataSet);

    /**
     * @return string
     */
    public function getCode();
}
