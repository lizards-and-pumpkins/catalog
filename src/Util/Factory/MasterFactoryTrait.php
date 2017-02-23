<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Factory;

use LizardsAndPumpkins\Util\Factory\Exception\UndefinedFactoryMethodException;

trait MasterFactoryTrait
{
    /**
     * @var Factory[]
     */
    private $methods = [];

    final public function register(Factory $factory)
    {
        if ($factory instanceof FactoryWithCallback) {
            $factory->beforeFactoryRegistrationCallback($this);
        }

        foreach ((new \ReflectionObject($factory))->getMethods() as $method) {
            $name = $method->getName();

            if ($method->isProtected() || $method->isPrivate()) {
                continue;
            }

            if ('create' !== substr($name, 0, 6) && 'get' !== substr($name, 0, 3)) {
                continue;
            }

            $this->methods[$name] = $factory;
        }

        $factory->setMasterFactory($this);

        if ($factory instanceof FactoryWithCallback) {
            $factory->factoryRegistrationCallback($this);
        }
    }
    
    final public function hasMethod(string $method): bool
    {
        return isset($this->methods[$method]);
    }

    /**
     * @param string $method
     * @param mixed[] $parameters
     * @return mixed
     */
    final public function __call(string $method, array $parameters)
    {
        if (!isset($this->methods[$method])) {
            throw new UndefinedFactoryMethodException(sprintf('Unknown method "%s"', $method));
        }

        return call_user_func_array([$this->methods[$method], $method], $parameters);
    }
}
