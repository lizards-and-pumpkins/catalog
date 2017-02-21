<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\Exception\NoConsoleCommandSpecifiedException;
use LizardsAndPumpkins\ConsoleCommand\ConsoleCommandFactory;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class CliBootstrap
{
    const ENV_DEBUG_VAR = 'LP_DEBUG_LOG';

    public static function create(string $cliCommandClass, Factory ...$factoriesToRegister): ConsoleCommand
    {
        $masterFactory = self::createMasterFactory(...$factoriesToRegister);

        return new $cliCommandClass($masterFactory, new CLImate());
    }

    /**
     * @param string[] $argv
     * @param Factory[] $factories
     * @return ConsoleCommand
     */
    public static function fromArgumentsVector(array $argv, Factory ...$factories): ConsoleCommand
    {
        if (! isset($argv[1])) {
            throw new NoConsoleCommandSpecifiedException('No command name specified.');
        }
        $consoleCommandFactory = new ConsoleCommandFactory();
        $masterFactory = self::createMasterFactory($consoleCommandFactory, ...$factories);
        $commandClass = self::getConsoleCommandLocator($masterFactory)->getClassFromName($argv[1]);
        
        return self::create($commandClass, $consoleCommandFactory, ...$factories);
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

    private static function getConsoleCommandLocator(MasterFactory $masterFactory): ConsoleCommandLocator
    {
        return $masterFactory->createConsoleCommandLocator();
    }
}
