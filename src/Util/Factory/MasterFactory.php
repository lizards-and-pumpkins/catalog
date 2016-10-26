<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Factory;

interface MasterFactory
{
    /**
     * @param Factory $factory
     * @return void
     */
    public function register(Factory $factory);
}
