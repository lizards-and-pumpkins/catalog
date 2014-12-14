<?php

namespace Brera\PoC;

interface EnvironmentBuilder
{
    /**
     * @param string $xmlString
     * @return Environment
     */
    public function createEnvironmentFromXml($xmlString);
}
