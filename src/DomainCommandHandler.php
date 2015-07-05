<?php

namespace Brera;

interface DomainCommandHandler
{
    /**
     * @return void
     */
    public function process();
}
