<?php

namespace Brera\Context;

use Brera\DataVersion;
use Brera\Http\HttpRequest;

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
     * @param HttpRequest $request
     * @return Context
     */
    public function createFromRequest(HttpRequest $request)
    {
        return $this->createContext(['request' => $request, 'locale' => 'en_US']);
    }

    /**
     * @param array[] $contextDataSets
     * @return Context[]
     */
    public function createContextsFromDataSets(array $contextDataSets)
    {
        array_map([$this, 'validateAllPartsHaveDecorators'], $contextDataSets);
        return array_map([$this, 'createContext'], $contextDataSets);
    }

    /**
     * @param mixed[] $contextDataSet
     */
    private function validateAllPartsHaveDecorators(array $contextDataSet)
    {
        array_map(function ($code) {
            $this->validateDecoratorClassExists($code, $this->getDecoratorClass($code));
        }, array_keys($contextDataSet));
    }

    /**
     * @param mixed[] $contextDataSet
     * @return Context
     */
    public function createContext(array $contextDataSet)
    {
        $versionedContext = new VersionedContext($this->dataVersion);
        $codes = $this->getContextDecoratorCodesToCreate($contextDataSet);
        return array_reduce($codes, function ($context, $code) use ($contextDataSet) {
            return $this->createContextDecorator($context, $code, $contextDataSet);
        }, $versionedContext);
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
        $qualifiedDecoratorClass = '\\Brera\\Context\\' . $decoratorClass;
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
    private function getContextDecoratorCodesToCreate(array $contextDataSet)
    {
        $dataSetCodes = array_diff(array_keys($contextDataSet), [VersionedContext::CODE]);
        $registeredDecoratorCodes = array_diff(array_keys($this->registeredContextDecorators), $dataSetCodes);
        $codes = array_merge($dataSetCodes, $registeredDecoratorCodes);
        sort($codes);
        return $codes;
    }
}
