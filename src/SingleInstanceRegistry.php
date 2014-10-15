<?php

namespace Brera\PoC;

interface SingleInstanceRegistry
{
    public function setMasterFactory(MasterFactory $masterFactory);
}
