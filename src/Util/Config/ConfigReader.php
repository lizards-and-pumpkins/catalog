<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Config;

interface ConfigReader
{
    public function has(string $configKey) : bool;

    /**
     * @param string $configKey
     * @return null|string
     */
    public function get(string $configKey);
}
