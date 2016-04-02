<?php

namespace LizardsAndPumpkins\Context\Stub;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextSource;

class StubContextSource extends ContextSource
{
    /**
     * @var array[]
     */
    private $testContextMatrix;

    /**
     * @param ContextBuilder $contextBuilder
     * @param array[] $testContextMatrix
     */
    public function __construct(ContextBuilder $contextBuilder, array $testContextMatrix)
    {
        parent::__construct($contextBuilder);
        $this->testContextMatrix = $testContextMatrix;
    }

    /**
     * @return array[]
     */
    protected function getContextMatrix()
    {
        return $this->testContextMatrix;
    }
}
