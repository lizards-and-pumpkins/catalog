<?php

namespace Brera\PoC;

interface Factory
{
    /**
     * @param MasterFactory $masterFactory
     * @return mixed
     */
    public function setMasterFactory(MasterFactory $masterFactory);
}
