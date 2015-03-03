<?php


namespace Brera\Context;

class ContextSource
{
    /**
     * @var array[]
     */
    private $contextMatrix;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    /**
     * @param array[] $contextMatrix
     * @param ContextBuilder $contextBuilder
     */
    public function __construct(array $contextMatrix, ContextBuilder $contextBuilder)
    {
        $this->contextMatrix = $contextMatrix;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param string $part
     * @return array[]
     */
    public function getContextValuesForPart($part)
    {
        if (!array_key_exists($part, $this->contextMatrix)) {
            return [];
        }
        return $this->contextMatrix[$part];
    }

    /**
     * @param string[] $partsToExtract
     * @return Context[]
     */
    public function extractContextsForParts(array $partsToExtract)
    {
        $variations = $this->extractCartesianProductOfContextsAsArray($partsToExtract);

        return $this->contextBuilder->getContexts($variations);
    }

    /**
     * @return Context[]
     */
    public function getAllAvailableContexts()
    {
        return $this->extractContextsForParts(array_keys($this->contextMatrix));
    }

    /**
     * @param string[] $partsToExtract
     * @return array[]
     */
    private function extractCartesianProductOfContextsAsArray(array $partsToExtract)
    {
        if (!$this->ifVersionIsASpecifiedPart($partsToExtract)) {
            $partsToExtract[] = VersionedContext::CODE;
        }
        return $this->getAllPossibleCombinationsRecursively($partsToExtract);
    }

    /**
     * @param string[] $partsToExtract
     * @return bool
     */
    private function ifVersionIsASpecifiedPart(array $partsToExtract)
    {
        return in_array(VersionedContext::CODE, $partsToExtract);
    }

    /**
     * @param string[] $partsToExtract
     * @return mixed[]
     */
    private function getContextsThatAreRequestedAndExistInTheSource(array $partsToExtract)
    {
        return array_intersect_key($this->contextMatrix, array_flip($partsToExtract));
    }

    /**
     * @param string[] $partsToExtract
     * @return array[]
     */
    private function getAllPossibleCombinationsRecursively(array $partsToExtract)
    {
        $presentContexts = $this->getContextsThatAreRequestedAndExistInTheSource($partsToExtract);
        return $this->buildRecursively($presentContexts);
    }

    /**
     * Thanks Jonathan H. Wage for https://gist.github.com/jwage/11193216
     *
     * @param array $set
     * @return array[]
     */
    private function buildRecursively(array $set)
    {
        if (!$set) {
            return [[]];
        }
        $key = key($set);
        $subset = array_shift($set);
        $cartesianSubset = $this->buildRecursively($set);
        return $this->addSubsetToResult($subset, $cartesianSubset, $key);
    }

    /**
     * @param array[] $subset
     * @param string[] $cartesianSubset
     * @param string $key
     * @return array[]
     */
    private function addSubsetToResult(array $subset, array $cartesianSubset, $key)
    {
        $result = [];
        foreach ($subset as $value) {
            foreach ($cartesianSubset as $p) {
                $p[$key] = $value;
                $result[] = $p;
            }
        }
        return $result;
    }
}
