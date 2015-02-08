<?php


namespace Brera\Environment;


class EnvironmentBuilder
{
    /**
     * @param array $environmentSourceDataSets
     * @return Environment[]
     */
    public function getEnvironments(array $environmentSourceDataSets)
    {
        return array_map([$this, 'createDecoratedEnvironmentSet'], $environmentSourceDataSets);
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
        $decoratorClass = ucfirst($this->removeUnderscores($code)) . 'EnvironmentDecorator';
        $qualifiedDecoratorClass = "\\Brera\\Environment\\$decoratorClass";
        $this->validateDecoratorClass($qualifiedDecoratorClass, $code);
        return $qualifiedDecoratorClass;
    }

    /**
     * @param string $qualifiedClassName
     * @param string $code
     * @throws EnvironmentDecoratorNotFoundException
     * @throws InvalidEnvironmentDecoratorClassException
     */
    private function validateDecoratorClass($qualifiedClassName, $code)
    {
        if (!class_exists($qualifiedClassName)) {
            throw new EnvironmentDecoratorNotFoundException(
                sprintf('No environment decorator found for code "%s"', $code)
            );
        }
        if (!in_array(EnvironmentDecorator::class, class_parents($qualifiedClassName))) {
            throw new InvalidEnvironmentDecoratorClassException(sprintf(
                'Environment Decorator class "%s" does not extend \Brera\EnvironmentDecorator', $qualifiedClassName
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
