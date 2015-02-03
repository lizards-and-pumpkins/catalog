<?php


namespace Brera;


class EnvironmentBuilder
{
    /**
     * @param array $environmentSourceDataSets
     * @return Environment[]
     * @throws EnvironmentDecoratorNotFoundException
     */
    public function getEnvironments(array $environmentSourceDataSets)
    {
        $result = [];
        foreach ($environmentSourceDataSets as $environmentSourceDataSet) {
            $versionedEnvironment = new VersionedEnvironment($environmentSourceDataSet);
            $environment = $versionedEnvironment;
            foreach ($environmentSourceDataSet as $key => $value) {
                if ($key == $versionedEnvironment->getCode()) {
                    continue;
                }
                $decoratorClass = $this->locateDecorator($key);
                if (! class_exists($decoratorClass)) {
                    throw new EnvironmentDecoratorNotFoundException(
                        sprintf('No environment decorator found for key "%s"', $key)
                    );
                }
                $environment = new $decoratorClass($environment, $environmentSourceDataSet);
            }
            $result[] = $environment;
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
