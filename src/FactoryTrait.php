<?php

namespace Brera;

trait FactoryTrait
{
    /**
     * @var MasterFactory
     */
    private $masterFactory;

    final public function setMasterFactory(MasterFactory $masterFactory)
    {
        $this->masterFactory = $masterFactory;
    }

    /**
     * @return MasterFactory
     * @throws NoMasterFactorySetException
     */
    protected function getMasterFactory()
    {
        if ($this->masterFactory === null) {
            throw new NoMasterFactorySetException('No master factory set');
        }

        return $this->masterFactory;
    }
}
