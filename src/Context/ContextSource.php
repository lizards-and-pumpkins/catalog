<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;

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
    public function getAllAvailableContexts() : array
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
    public function getContextsForParts(array $requestedContextParts) : array
    {
        $contextDataSets = $this->getContextMatrixForParts($requestedContextParts);
        
        return $this->contextBuilder->createContextsFromDataSets($contextDataSets);
    }

    /**
     * @return array[]
     */
    abstract protected function getContextMatrix() : array;

    /**
     * @param string[] $requestedParts
     * @return array[]
     */
    private function getContextMatrixForParts(array $requestedParts) : array
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
    private function extractMatchingParts(array $contextSourceRecord, array $flippedRequestedParts) : array
    {
        return array_intersect_key($contextSourceRecord, $flippedRequestedParts);
    }

    /**
     * @param string[] $matchingContextParts
     * @param array[] $aggregatedResult
     * @return array[]
     */
    private function addExtractedContextToAggregateIfNotAlreadyPresent(
        array $matchingContextParts,
        array $aggregatedResult
    ) : array {
        if (!in_array($matchingContextParts, $aggregatedResult)) {
            $aggregatedResult[] = $matchingContextParts;
        }
        return $aggregatedResult;
    }

    /**
     * @param DataVersion $version
     * @return Context[]
     */
    public function getAllAvailableContextsWithVersionApplied(DataVersion $version) : array
    {
        return $this->contextBuilder->createContextsFromDataSets(
            array_map(function (array $dataSet) use ($version) {
                return array_merge($dataSet, [DataVersion::CONTEXT_CODE => (string) $version]);
            }, $this->getContextMatrix())
        );
    }
}
