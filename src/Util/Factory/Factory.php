<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Factory;

interface Factory
{
    /**
     * @param MasterFactory $masterFactory
     * @return mixed
     */
    public function setMasterFactory(MasterFactory $masterFactory);
}
