<?php


namespace LizardsAndPumpkins\Context\ContextBuilder;

interface ContextPartBuilder
{
    /**
     * @param mixed[] $inputDataSet
     * @return string
     */
    public function getValue(array $inputDataSet);

    /**
     * @return string
     */
    public function getCode();
}
