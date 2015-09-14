<?php

namespace LizardsAndPumpkins;

interface DomainEventHandler
{
    /**
     * @return void
     */
    public function process();
}
