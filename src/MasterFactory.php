<?php

namespace Brera\PoC;

interface MasterFactory
{
    /**
     * @param Factory $factory
     * @return null
     */
    public function register(Factory $factory);
}
