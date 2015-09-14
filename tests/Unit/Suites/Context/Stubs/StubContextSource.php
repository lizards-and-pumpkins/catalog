<?php


namespace LizardsAndPumpkins\Context\Stubs;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextSource;

class StubContextSource extends ContextSource
{
    /**
     * @var string[]
     */
    private $testContextMatrix;

    /**
     * @param ContextBuilder $contextBuilder
     * @param mixed[] $testContextMatrix
     */
    public function __construct(ContextBuilder $contextBuilder, array $testContextMatrix)
    {

        // TODO is $testContextMatrix mixed[] ?
        parent::__construct($contextBuilder);
        $this->testContextMatrix = $testContextMatrix;
    }

    /**
     * @return string[]
     */
    protected function getContextMatrix()
    {
        return $this->testContextMatrix;
    }
}
