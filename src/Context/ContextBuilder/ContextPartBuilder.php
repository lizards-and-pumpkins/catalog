<?php


namespace LizardsAndPumpkins\Context\ContextBuilder;

interface ContextPartBuilder
{
    /**
     * @param mixed[] $inputDataSet
     * @param string[] $otherContextParts
     * @return string
     */
    public function getValue(array $inputDataSet, array $otherContextParts);

    /**
     * @return string
     */
    public function getCode();
}
