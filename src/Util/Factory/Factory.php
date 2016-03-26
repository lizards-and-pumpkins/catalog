<?php

namespace LizardsAndPumpkins\Util\Factory;

interface Factory
{
    /**
     * @param MasterFactory $masterFactory
     * @return mixed
     */
    public function setMasterFactory(MasterFactory $masterFactory);
}
