<?php

namespace LizardsAndPumpkins\Util\Factory;

interface FactoryWithCallback
{
    /**
     * @param MasterFactory $masterFactory
     * @return void
     */
    public function factoryRegistrationCallback(MasterFactory $masterFactory);
}
