<?php

namespace LizardsAndPumpkins\Util\Factory;

interface RegistersDelegateFactory
{
    /**
     * @param MasterFactory $masterFactory
     * @return void
     */
    public function registerDelegateFactories(MasterFactory $masterFactory);
}
