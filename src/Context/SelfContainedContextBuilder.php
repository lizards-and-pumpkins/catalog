<?php

declare(strict_types=1);

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
    public function createContext(array $inputDataSet) : Context
    {
        $contextDataSet = array_reduce(
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
        return new SelfContainedContext($contextDataSet);
    }

    public function createFromRequest(HttpRequest $request) : Context
    {
        return $this->createContext([self::REQUEST => $request]);
    }

    /**
     * @param array[] $contextDataSets
     * @return Context[]
     */
    public function createContextsFromDataSets(array $contextDataSets) : array
    {
        return array_map([$this, 'createContext'], $contextDataSets);
    }

    /**
     * @param string[] $dataSet
     * @return Context
     */
    public static function rehydrateContext(array $dataSet) : Context
    {
        return new SelfContainedContext($dataSet);
    }

    /**
     * @param Context $context
     * @param string[] $additionDataSet
     * @return Context
     */
    public function expandContext(Context $context, array $additionDataSet) : Context
    {
        $originalDataSet = $this->extractDataSetFromContext($context);
        return $this->createContext(array_merge($originalDataSet, $additionDataSet));
    }

    /**
     * @param Context $context
     * @return string[]
     */
    private function extractDataSetFromContext(Context $context) : array
    {
        return array_reduce($context->getSupportedCodes(), function ($carry, $code) use ($context) {
            return array_merge($carry, [$code => $context->getValue($code)]);
        }, []);
    }
}
