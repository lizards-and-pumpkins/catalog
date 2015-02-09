<?php

namespace Brera;

interface DomainEventHandler
{
    /**
     * @return null
     */
    public function process();
}
