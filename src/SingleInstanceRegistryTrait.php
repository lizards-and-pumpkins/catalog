<?php

namespace Brera\PoC;

trait SingleInstanceRegistryTrait
{
    /**
     * @var array
     */
    private $instances = [];

    /**
     * @var MasterFactory
     */
    private $masterFactory;

    /**
     * @param MasterFactory $masterFactory
     */
    final public function setMasterFactory(MasterFactory $masterFactory)
    {
        $this->masterFactory = $masterFactory;
    }

    /**
     * @return MasterFactory
     * @throws NoMasterFactorySetException
     */
    final protected function getMasterFactory()
    {
        if ($this->masterFactory === null) {
            throw new NoMasterFactorySetException('No master factory set');
        }

        return $this->masterFactory;
    }

    /**
     * @param $name
     * @return mixed
     */
    final protected function createSingleInstance($name)
    {
        if (!$this->hasInstance($name)) {
            $method = 'create' . $name;
            $this->addInstance($this->getMasterFactory()->$method(), $name);
        }

        return $this->getInstance($name);
    }

    protected function hasInstance($name)
    {
        return isset($this->instances[$name]);
    }

    protected function getInstance($name)
    {
        if (!$this->hasInstance($name)) {
            throw new NoMasterFactorySetException(sprintf('No instance "%s"', $name));
        }

        return $this->instances[$name];
    }

    protected function addInstance($instance, $name)
    {
        if (!is_object($instance)) {
            throw new NoMasterFactorySetException(sprintf('"%s" is no object instance', $instance));
        }

        $this->instances[$name] = $instance;
    }
}
