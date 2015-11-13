<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\NoMasterFactorySetException;

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
     */
    protected function getMasterFactory()
    {
        if ($this->masterFactory === null) {
            throw new NoMasterFactorySetException('No master factory set');
        }

        return $this->masterFactory;
    }
}
