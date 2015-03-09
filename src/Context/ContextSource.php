<?php

namespace Brera\Context;

abstract class ContextSource
{
    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    /**
     * @param ContextBuilder $contextBuilder
     */
    public function __construct(ContextBuilder $contextBuilder)
    {
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @return Context[]
     */
    public function getAllAvailableContexts()
    {
        return $this->contextBuilder->getContexts($this->getContextMatrix());
    }

    abstract protected function getContextMatrix();
}
