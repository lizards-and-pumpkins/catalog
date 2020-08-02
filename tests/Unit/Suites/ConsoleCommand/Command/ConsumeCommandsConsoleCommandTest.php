<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use LizardsAndPumpkins\ConsoleCommand\ConsoleCommand;
use LizardsAndPumpkins\Messaging\Command\CommandConsumer;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\ConsumeCommandsConsoleCommand
 */
class ConsumeCommandsConsoleCommandTest extends TestCase
{
    /**
     * @var MasterFactory
     */
    private $stubMasterFactory;

    final protected function setUp(): void
    {
        $this->stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->onlyMethods(get_class_methods(MasterFactory::class))
            ->addMethods(['createCommandConsumer'])
            ->disableOriginalConstructor()
            ->getMock();
    }
    
    public function testIsAConsoleCommand(): void
    {
        $command = new ConsumeCommandsConsoleCommand($this->stubMasterFactory);
        $this->assertInstanceOf(ConsoleCommand::class, $command);
    }

    public function testCallsProcessOnTheCommandConsumer(): void
    {
        $mockCommandConsumer = $this->createMock(CommandConsumer::class);
        $mockCommandConsumer->expects($this->once())->method('process');
        $this->stubMasterFactory->method('createCommandConsumer')->willReturn($mockCommandConsumer);
        (new ConsumeCommandsConsoleCommand($this->stubMasterFactory))->run();
    }
}
