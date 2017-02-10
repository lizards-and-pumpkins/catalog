<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use League\CLImate\CLImate;
use LizardsAndPumpkins\Util\Factory\Factory;

class CliBootstrap
{
    public static function create(string $cliCommandClass, Factory ...$factoriesToRegister): BaseCliCommand
    {
        return new $cliCommandClass(CliFactoryBootstrap::createMasterFactory(...$factoriesToRegister), new CLImate());
    }
}
