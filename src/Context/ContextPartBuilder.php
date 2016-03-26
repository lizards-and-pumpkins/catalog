<?php


namespace LizardsAndPumpkins\Context;

interface ContextPartBuilder
{
    /**
     * @param mixed[] $inputDataSet
     * @param string[] $otherContextParts
     * @return string|null
     */
    public function getValue(array $inputDataSet, array $otherContextParts);

    /**
     * @return string
     */
    public function getCode();
}
