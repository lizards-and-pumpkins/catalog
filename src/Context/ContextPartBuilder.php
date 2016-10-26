<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context;

interface ContextPartBuilder
{
    /**
     * @param mixed[] $inputDataSet
     * @return string|null
     */
    public function getValue(array $inputDataSet);

    public function getCode() : string;
}
