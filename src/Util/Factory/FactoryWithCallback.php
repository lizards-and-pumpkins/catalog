<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Factory;

interface FactoryWithCallback
{
    /**
     * @param MasterFactory $masterFactory
     * @return void
     */
    public function factoryRegistrationCallback(MasterFactory $masterFactory);

    /**
     * @param MasterFactory $masterFactory
     * @return void
     */
    public function beforeFactoryRegistrationCallback(MasterFactory $masterFactory);
}
