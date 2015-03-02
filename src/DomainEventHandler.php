<?php

namespace Brera;

interface DomainEventHandler
{
    public function process();
}
