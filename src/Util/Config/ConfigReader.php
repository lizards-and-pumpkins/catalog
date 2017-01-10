<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Config;

interface ConfigReader
{
    public function has(string $configKey) : bool;

    public function get(string $configKey) : string;
}
