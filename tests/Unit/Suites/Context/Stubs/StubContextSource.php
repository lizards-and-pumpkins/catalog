<?php


namespace Brera\Context\Stubs;

use Brera\Context\ContextBuilder;
use Brera\Context\ContextSource;

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
