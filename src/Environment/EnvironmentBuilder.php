<?php


namespace Brera\Environment;

use Brera\DataVersion;
use Brera\Http\HttpRequest;

class EnvironmentBuilder
{
    /**
     * @var string[]
     */
    private $registeredEnvironmentDecorators = [];
    /**
     * @var
     */
    private $dataVersion;

    /**
     * @param DataVersion $dataVersion
     */
    public function __construct(DataVersion $dataVersion)
    {
        $this->dataVersion = $dataVersion;
    }

    /**
     * @param HttpRequest $request
     * @return Environment
     */
    public function createFromRequest(HttpRequest $request)
    {
        return $this->getEnvironment(['website' => 'ru_de', 'language' => 'de_DE']);
    }

    /**
     * @param string[] $environmentDataSets
     * @return Environment[]
     */
    public function getEnvironments(array $environmentDataSets)
    {
        return array_map([$this, 'getEnvironment'], $environmentDataSets);
    }

    /**
     * @param array $environmentDataSet
     * @return Environment
     */
    public function getEnvironment(array $environmentDataSet)
    {
        $versionedEnvironment = new VersionedEnvironment($this->dataVersion);
        $codes = array_diff(array_keys($environmentDataSet), [VersionedEnvironment::CODE]);
        return array_reduce($codes, function ($environment, $code) use ($environmentDataSet) {
            return $this->createEnvironmentDecorator($environment, $code, $environmentDataSet);
        }, $versionedEnvironment);
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
