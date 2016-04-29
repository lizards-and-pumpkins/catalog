<?php

namespace LizardsAndPumpkins\Context;

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
                $value = $builder->getValue($inputDataSet);

                if (null === $value) {
                    return $carry;
                }

                return array_merge($carry, [$builder->getCode() => $value]);
            },
            []
        );
        return SelfContainedContext::fromArray($contextDataSet);
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

    /**
     * @param Context $context
     * @param string[] $additionDataSet
     * @return Context
     */
    public function expandContext(Context $context, array $additionDataSet)
    {
        $originalDataSet = $this->extractDataSetFromContext($context);
        return $this->createContext(array_merge($originalDataSet, $additionDataSet));
    }

    /**
     * @param Context $context
     * @return string[]
     */
    private function extractDataSetFromContext(Context $context)
    {
        return array_reduce($context->getSupportedCodes(), function ($carry, $code) use ($context) {
            return array_merge($carry, [$code => $context->getValue($code)]);
        }, []);
    }
}
