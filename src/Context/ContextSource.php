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

    /**
     * @param string[] $requestedContextParts
     * @return Context[]
     */
    public function getContextsForParts(array $requestedContextParts)
    {
        return $this->contextBuilder->getContexts($this->getContextMatrixForParts($requestedContextParts));
    }

    /**
     * @return array[]
     */
    abstract protected function getContextMatrix();

    /**
     * @param string[] $requestedParts
     * @return array[]
     */
    private function getContextMatrixForParts(array $requestedParts)
    {
        $flippedRequestedParts = array_flip($requestedParts);
        $extractRequestedParts = function (array $contextSourceRecord) use ($flippedRequestedParts) {
            return array_intersect_key($contextSourceRecord, $flippedRequestedParts);
        };
        $removeDupes = function(array $result, array $record) {
            return in_array($record, $result) ?
                $result :
                array_merge($result, [$record]);
        };
        return array_reduce(array_map($extractRequestedParts, $this->getContextMatrix()), $removeDupes, []);
    }
}
