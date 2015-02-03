<?php


namespace Brera;


class EnvironmentBuilder
{
    /**
     * @param array $environments
     * @return array
     * @throws EnvironmentDecoratorNotFoundException
     */
    public function getEnvironments(array $environments)
    {
        $result = [];
        foreach ($environments as $environmentData) {
            $versionedEnvironment = new VersionedEnvironment($environmentData);
            $environment = $versionedEnvironment;
            foreach ($environmentData as $key => $value) {
                if ($key == $versionedEnvironment->getCode()) {
                    continue;
                }
                $decoratorClass = $this->locateDecorator($key);
                if (! class_exists($decoratorClass)) {
                    throw new EnvironmentDecoratorNotFoundException(
                        sprintf('No environment decorator found for key "%s"', $key)
                    );
                }
                $result[] = new $decoratorClass($environment, $environmentData);
            }
        }
        return $result;
    }

    /**
     * @param string $key
     * @return string
     */
    private function locateDecorator($key)
    {
        $class = ucfirst($key) . 'EnvironmentDecorator'; // todo: remove underscores
        return $class;
    }
}
