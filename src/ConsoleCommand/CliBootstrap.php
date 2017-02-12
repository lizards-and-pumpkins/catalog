<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use League\CLImate\CLImate;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class CliBootstrap
{
    const ENV_DEBUG_VAR = 'LP_DEBUG_LOG';

    public static function create(string $cliCommandClass, Factory ...$factoriesToRegister): BaseCliCommand
    {
        $masterFactory = self::createMasterFactory(...$factoriesToRegister);

        return new $cliCommandClass($masterFactory, new CLImate());
    }

    private static function createMasterFactory(Factory ...$factoriesToRegister): MasterFactory
    {
        return self::isLoggingActive() ?
            CliFactoryBootstrap::createLoggingMasterFactory(...$factoriesToRegister) :
            CliFactoryBootstrap::createMasterFactory(...$factoriesToRegister);
    }

    private static function isLoggingActive(): bool
    {
        return ($_SERVER[self::ENV_DEBUG_VAR] ?? false) || ($_ENV[self::ENV_DEBUG_VAR] ?? false);
    }
}
