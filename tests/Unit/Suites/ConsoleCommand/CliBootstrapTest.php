<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\TestDouble\MockCliCommand;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\CliBootstrap
 * @uses   \LizardsAndPumpkins\ConsoleCommand\CliFactoryBootstrap
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 */
class CliBootstrapTest extends TestCase
{
    public function testReturnsAnInstanceOfTheSpecifiedCommand()
    {
        $command = CliBootstrap::create(MockCliCommand::class);
        $this->assertInstanceOf(MockCliCommand::class, $command);
    }

    public function testInjectsMasterFactoryAndCLIMateInstanceToCommand()
    {
        /** @var MockCliCommand $command */
        $command = CliBootstrap::create(MockCliCommand::class);
        $this->assertInstanceOf(MasterFactory::class, $command->factory);
        $this->assertInstanceOf(CLImate::class, $command->cliMate);
    }

    public function testRegistersSpecifiedFactoriesWithMasterFactory()
    {
        $spyFactory = new class implements Factory, FactoryWithCallback {
            use FactoryTrait;

            public $wasRegistered = false;

            public function factoryRegistrationCallback(MasterFactory $masterFactory)
            {
                $this->wasRegistered = true;
            }
        };
        /** @var MockCliCommand $command */
        CliBootstrap::create(MockCliCommand::class, $spyFactory);
        
        $this->assertTrue($spyFactory->wasRegistered);
    }
}
