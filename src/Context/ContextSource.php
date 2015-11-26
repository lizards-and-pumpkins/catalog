<?php

namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\DataVersion;

abstract class ContextSource
{
    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    /**
     * @var Context[]
     */
    private $memoizedAllAvailableContexts;

    public function __construct(ContextBuilder $contextBuilder)
    {
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @return Context[]
     */
    public function getAllAvailableContexts()
    {
        if (null === $this->memoizedAllAvailableContexts) {
            $this->memoizedAllAvailableContexts = $this->contextBuilder->createContextsFromDataSets(
                $this->getContextMatrix()
            );
        }

        return $this->memoizedAllAvailableContexts;
    }

    /**
     * @param string[] $requestedContextParts
     * @return Context[]
     */
    public function getContextsForParts(array $requestedContextParts)
    {
        $contextDataSets = $this->getContextMatrixForParts($requestedContextParts);
        
        return $this->contextBuilder->createContextsFromDataSets($contextDataSets);
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
        $flippedRequestedParts = array_flip($requestedParts);
        foreach ($this->getContextMatrix() as $contextSourceRecord) {
            $matchingParts = $this->extractMatchingParts($contextSourceRecord, $flippedRequestedParts);
            $aggregatedResult = $this->addExtractedContextToAggregateIfNotAlreadyPresent(
                $matchingParts,
                $aggregatedResult
            );
        }
        return $aggregatedResult;
    }

    /**
     * @param string[] $contextSourceRecord
     * @param string[] $flippedRequestedParts
     * @return string[]
     */
    private function extractMatchingParts($contextSourceRecord, $flippedRequestedParts)
    {
        return array_intersect_key($contextSourceRecord, $flippedRequestedParts);
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

    /**
     * @param DataVersion $version
     * @return Context[]
     */
    public function getAllAvailableContextsWithVersion(DataVersion $version)
    {
        return $this->contextBuilder->createContextsFromDataSets(
            array_map(function (array $dataSet) use ($version) {
                return array_merge($dataSet, [VersionedContext::CODE => (string) $version]);
            }, $this->getContextMatrix())
        );
    }
}
