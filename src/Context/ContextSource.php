<?php

namespace Brera\Context;

abstract class ContextSource
{
    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    /**
     * @var Context[]
     */
    private $lazyLoadedAllAvailableContexts;

    public function __construct(ContextBuilder $contextBuilder)
    {
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @return Context[]
     */
    public function getAllAvailableContexts()
    {
        if (null === $this->lazyLoadedAllAvailableContexts) {
            $this->lazyLoadedAllAvailableContexts = $this->contextBuilder->getContexts($this->getContextMatrix());
        }

        return $this->lazyLoadedAllAvailableContexts;
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
        $aggregatedResult = [];
        foreach ($this->getContextMatrix() as $contextSourceRecord) {
            $matchingParts = $this->extractMatchingParts($contextSourceRecord, $requestedParts);
            $aggregatedResult = $this->addExtractedContextToAggregateIfNotAlreadyPresent(
                $matchingParts,
                $aggregatedResult
            );
        }
        return $aggregatedResult;
    }

    /**
     * @param string[] $contextSourceRecord
     * @param string[] $requestedParts
     * @return string[]
     */
    private function extractMatchingParts($contextSourceRecord, $requestedParts)
    {
        return array_intersect_key($contextSourceRecord, array_flip($requestedParts));
    }

    /**
     * @param string[] $matchingContextParts
     * @param array[] $aggregatedResult
     * @return array[]
     */
    private function addExtractedContextToAggregateIfNotAlreadyPresent($matchingContextParts, $aggregatedResult)
    {
        if (!in_array($matchingContextParts, $aggregatedResult)) {
            $aggregatedResult[] = $matchingContextParts;
        }
        return $aggregatedResult;
    }
}
