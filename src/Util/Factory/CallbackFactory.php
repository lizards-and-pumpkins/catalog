<?php

namespace LizardsAndPumpkins\Util\Factory;

interface CallbackFactory
{
    /**
     * @param MasterFactory $masterFactory
     * @return void
     */
    public function factoryRegistrationCallback(MasterFactory $masterFactory);
}
