<?php


namespace Brera\Environment;

class EnvironmentBuilder
{
    /**
     * @var string[]
     */
    private $registeredEnvironmentDecorators = [];

    /**
     * @param array $environmentSourceDataSets
     * @return Environment[]
     */
    public function getEnvironments(array $environmentSourceDataSets)
    {
        return array_map([$this, 'createDecoratedEnvironmentSet'], $environmentSourceDataSets);
    }

    /**
     * @param string $code
     * @param string $environmentDecoratorClass
     */
    public function registerEnvironmentDecorator($code, $environmentDecoratorClass)
    {
        $this->validateDecoratorClass($code, $environmentDecoratorClass);
        $this->registeredEnvironmentDecorators[$code] = $environmentDecoratorClass;
    }

    /**
     * @param array $environmentSourceDataSet
     * @return Environment
     */
    private function createDecoratedEnvironmentSet(array $environmentSourceDataSet)
    {
        $versionedEnvironment = new VersionedEnvironment($environmentSourceDataSet);
        $codes = array_diff(array_keys($environmentSourceDataSet), [VersionedEnvironment::CODE]);
        return array_reduce($codes, function ($environment, $code) use ($environmentSourceDataSet) {
            return $this->createEnvironmentDecorator($environment, $code, $environmentSourceDataSet);
        }, $versionedEnvironment);
    }

    /**
     * @param Environment $environment
     * @param string $code
     * @param array $environmentSourceDataSet
     * @return EnvironmentDecorator
     */
    private function createEnvironmentDecorator(Environment $environment, $code, array $environmentSourceDataSet)
    {
        $decoratorClass = $this->getDecoratorClass($code);
        return new $decoratorClass($environment, $environmentSourceDataSet);
    }

    /**
     * @param string $code
     * @return string
     */
    private function getDecoratorClass($code)
    {
        return array_key_exists($code, $this->registeredEnvironmentDecorators) ?
            $this->registeredEnvironmentDecorators[$code] :
            $this->getDefaultEnvironmentDecoratorClass($code);
    }

    /**
     * @param string $code
     * @return string
     */
    private function getDefaultEnvironmentDecoratorClass($code)
    {
        $decoratorClass = ucfirst($this->removeUnderscores($code)) . 'EnvironmentDecorator';
        $qualifiedDecoratorClass = "\\Brera\\Environment\\$decoratorClass";
        $this->validateDecoratorClass($code, $qualifiedDecoratorClass);
        $this->registerEnvironmentDecorator($code, $qualifiedDecoratorClass);
        return $qualifiedDecoratorClass;
    }

    /**
     * @param string $code
     * @param string $qualifiedClassName
     * @throws EnvironmentDecoratorNotFoundException
     * @throws InvalidEnvironmentDecoratorClassException
     */
    private function validateDecoratorClass($code, $qualifiedClassName)
    {
        if (!class_exists($qualifiedClassName)) {
            throw new EnvironmentDecoratorNotFoundException(
                sprintf('Environment decorator class "%s" not found for code "%s"', $qualifiedClassName, $code)
            );
        }
        if (!in_array(EnvironmentDecorator::class, class_parents($qualifiedClassName))) {
            throw new InvalidEnvironmentDecoratorClassException(sprintf(
                'Environment Decorator class "%s" does not extend \\Brera\\Environment\\EnvironmentDecorator',
                $qualifiedClassName
            ));
        }
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
}
