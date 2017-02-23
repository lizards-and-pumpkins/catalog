<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Util\Factory;

trait FactoryCallbackTrait
{
    use FactoryTrait;
    
    public function beforeFactoryRegistrationCallback(MasterFactory $masterFactory)
    {
        // Hook method intentionally left empty
    }

    public function factoryRegistrationCallback(MasterFactory $masterFactory)
    {
        // Hook method intentionally left empty
    }
}
