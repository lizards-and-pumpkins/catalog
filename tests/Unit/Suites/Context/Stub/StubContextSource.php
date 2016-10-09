<?php

declare(strict_types=1);

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
    final public function __construct(ContextBuilder $contextBuilder, array $testContextMatrix)
    {
        parent::__construct($contextBuilder);
        $this->testContextMatrix = $testContextMatrix;
    }

    /**
     * @return array[]
     */
    final protected function getContextMatrix() : array
    {
        return $this->testContextMatrix;
    }
}
