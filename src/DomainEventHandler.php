<?php

namespace Brera;

interface DomainEventHandler
{
    /**
     * @return void
     */
    public function process();
}
