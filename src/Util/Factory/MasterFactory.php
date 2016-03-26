<?php

namespace LizardsAndPumpkins\Util\Factory;

interface MasterFactory
{
    /**
     * @param Factory $factory
     * @return null
     */
    public function register(Factory $factory);
}
