<?php


namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\ContextBuilder\ContextPartBuilder;
use LizardsAndPumpkins\Http\HttpRequest;

class SelfContainedContextBuilder implements ContextBuilder
{
    /**
     * @var ContextPartBuilder[]
     */
    private $partBuilders;

    public function __construct(ContextPartBuilder ...$partBuilders)
    {
        $this->partBuilders = $partBuilders;
    }

    /**
     * @param mixed[] $inputDataSet
     * @return Context
     */
    public function createContext(array $inputDataSet)
    {
        $contextDataSet = @array_reduce(
            $this->partBuilders,
            function ($carry, ContextPartBuilder $builder) use ($inputDataSet) {
                return array_merge($carry, $this->getPart($builder, $inputDataSet, $carry));
            },
            []
        );
        return SelfContainedContext::fromArray($contextDataSet);
    }

    /**
     * @param ContextPartBuilder $partBuilder
     * @param mixed[] $inputDataSet
     * @param string[] $carry
     * @return string[]
     */
    private function getPart(ContextPartBuilder $partBuilder, array $inputDataSet, array $carry)
    {
        return [$partBuilder->getCode() => $partBuilder->getValue($inputDataSet, $carry)];
    }

    /**
     * @param HttpRequest $request
     * @return Context
     */
    public function createFromRequest(HttpRequest $request)
    {
        return $this->createContext([self::REQUEST => $request]);
    }

    /**
     * @param array[] $contextDataSets
     * @return Context[]
     */
    public function createContextsFromDataSets(array $contextDataSets)
    {
        return array_map([$this, 'createContext'], $contextDataSets);
    }

    /**
     * @param string[] $dataSet
     * @return Context
     */
    public static function rehydrateContext(array $dataSet)
    {
        return SelfContainedContext::fromArray($dataSet);
    }
}
