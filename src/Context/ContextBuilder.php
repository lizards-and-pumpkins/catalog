<?php

namespace LizardsAndPumpkins\Context;

use LizardsAndPumpkins\Context\Exception\ContextDecoratorNotFoundException;
use LizardsAndPumpkins\Context\Exception\InvalidContextDecoratorClassException;
use LizardsAndPumpkins\Context\Exception\DataVersionMissingInContextDataSetException;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Http\HttpRequest;

class ContextBuilder
{
    /**
     * @var string[]
     */
    private $registeredContextDecorators = [];
    /**
     * @var
     */
    private $dataVersion;

    public function __construct(DataVersion $dataVersion)
    {
        $this->dataVersion = $dataVersion;
    }

    /**
     * @param array[] $dataSet
     * @return Context
     */
    public static function rehydrateContext(array $dataSet)
    {
        if (! isset($dataSet[VersionedContext::CODE])) {
            throw new DataVersionMissingInContextDataSetException(
                'The data version has to be part of the data set when using the static context factory method.'
            );
        }
        $builder = new self(DataVersion::fromVersionString($dataSet[VersionedContext::CODE]));
        return $builder->createContext($dataSet);
    }

    /**
     * @param HttpRequest $request
     * @return Context
     */
    public function createFromRequest(HttpRequest $request)
    {
        return $this->createContext(['request' => $request]);
    }

    /**
     * @param array[] $contextDataSets
     * @return Context[]
     */
    public function createContextsFromDataSets(array $contextDataSets)
    {
        array_map([$this, 'validateAllPartsHaveDecorators'], $contextDataSets);
        return array_map([$this, 'createContextContainingGivenContextParts'], $contextDataSets);
    }

    /**
     * @param mixed[] $contextDataSet
     */
    private function validateAllPartsHaveDecorators(array $contextDataSet)
    {
        array_map(function ($code) {
            if ($code === VersionedContext::CODE) {
                return;
            }
            $this->validateDecoratorClassExists($code, $this->getDecoratorClass($code));
        }, array_keys($contextDataSet));
    }

    /**
     * @param mixed[] $contextDataSet
     * @return Context
     */
    public function createContext(array $contextDataSet)
    {
        $decoratorCodes = $this->getContextCodesFromDataSetAndRegisteredCodes($contextDataSet);
        return $this->createContextForGivenCodes($contextDataSet, $decoratorCodes);
    }

    /**
     * @param mixed[] $contextDataSet
     * @return Context
     */
    private function createContextContainingGivenContextParts(array $contextDataSet)
    {
        $decoratorCodes = $this->getContextCodesFromDataSet($contextDataSet);
        return $this->createContextForGivenCodes($contextDataSet, $decoratorCodes);
    }

    /**
     * @param mixed[] $contextDataSet
     * @param string[] $decoratorCodes
     * @return Context
     */
    private function createContextForGivenCodes(array $contextDataSet, $decoratorCodes)
    {
        $versionedContext = new VersionedContext($this->getVersionForContext($contextDataSet));
        return array_reduce($decoratorCodes, function ($context, $code) use ($contextDataSet) {
            return $this->createContextDecorator($context, $code, $contextDataSet);
        }, $versionedContext);
    }

    /**
     * @param mixed[] $contextDataSet
     * @return DataVersion
     */
    private function getVersionForContext(array $contextDataSet)
    {
        return isset($contextDataSet[VersionedContext::CODE]) ?
            DataVersion::fromVersionString($contextDataSet[VersionedContext::CODE]) :
            $this->dataVersion;
    }

    /**
     * @param Context $context
     * @param string $code
     * @param string[] $contextSourceDataSet
     * @return ContextDecorator
     */
    private function createContextDecorator(Context $context, $code, array $contextSourceDataSet)
    {
        $decoratorClass = $this->getDecoratorClass($code);
        return class_exists($decoratorClass) ?
            new $decoratorClass($context, $contextSourceDataSet) :
            $context;
    }

    /**
     * @param string $code
     * @param string $contextDecoratorClass
     */
    public function registerContextDecorator($code, $contextDecoratorClass)
    {
        $this->validateDecoratorClass($code, $contextDecoratorClass);
        $this->registeredContextDecorators[$code] = $contextDecoratorClass;
    }

    /**
     * @param string $code
     * @param string $decoratorClass
     */
    private function validateDecoratorClass($code, $decoratorClass)
    {
        $this->validateDecoratorClassExists($code, $decoratorClass);
        $this->validateClassIsContextDecorator($code, $decoratorClass);
    }

    /**
     * @param string $code
     * @param string $decoratorClass
     */
    private function validateDecoratorClassExists($code, $decoratorClass)
    {
        if (!class_exists($decoratorClass)) {
            throw new ContextDecoratorNotFoundException(
                sprintf('Context decorator class "%s" not found for code "%s"', $decoratorClass, $code)
            );
        }
    }

    /**
     * @param string $code
     * @param string $decoratorClass
     */
    private function validateClassIsContextDecorator($code, $decoratorClass)
    {
        if (!in_array(ContextDecorator::class, class_parents($decoratorClass))) {
            throw new InvalidContextDecoratorClassException(sprintf(
                'Context Decorator class "%s" for code "%s" does not extend %s',
                $decoratorClass,
                $code,
                ContextDecorator::class
            ));
        }
    }

    /**
     * @param string $code
     * @return string
     */
    private function getDecoratorClass($code)
    {
        return array_key_exists($code, $this->registeredContextDecorators) ?
            $this->registeredContextDecorators[$code] :
            $this->getDefaultContextDecoratorClass($code);
    }

    /**
     * @param string $code
     * @return string
     */
    private function getDefaultContextDecoratorClass($code)
    {
        $decoratorClass = ucfirst($this->removeUnderscores($code)) . 'ContextDecorator';
        $qualifiedDecoratorClass = '\\LizardsAndPumpkins\\Context\\' . $decoratorClass;
        if (class_exists($qualifiedDecoratorClass)) {
            $this->registerContextDecorator($code, $qualifiedDecoratorClass);
        }
        return $qualifiedDecoratorClass;
    }

    /**
     * @param string $code
     * @return string
     */
    private function removeUnderscores($code)
    {
        return str_replace('_', '', preg_replace_callback('/_([a-z])/', function ($m) {
            return strtoupper($m[1]);
        }, $code));
    }

    /**
     * @param string[] $contextDataSet
     * @return string[]
     */
    private function getContextCodesFromDataSetAndRegisteredCodes(array $contextDataSet)
    {
        $dataSetCodes = $this->getContextCodesFromDataSet($contextDataSet);
        $registeredDecoratorCodes = array_diff($this->getRegisteredContextCodes(), $dataSetCodes);
        $codes = array_merge($dataSetCodes, $registeredDecoratorCodes);
        sort($codes);
        return $codes;
    }

    /**
     * @param mixed[] $contextDataSet
     * @return string[]
     */
    private function getContextCodesFromDataSet(array $contextDataSet)
    {
        $codes = array_diff(array_keys($contextDataSet), [VersionedContext::CODE]);
        sort($codes);
        return $codes;
    }

    /**
     * @return string[]
     */
    private function getRegisteredContextCodes()
    {
        return array_keys($this->registeredContextDecorators);
    }
}
