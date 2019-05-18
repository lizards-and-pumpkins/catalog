<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use LizardsAndPumpkins\ConsoleCommand\ConsoleCommand;
use LizardsAndPumpkins\Messaging\Command\CommandConsumer;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\ConsumeCommandsConsoleCommand
 */
class ConsumeCommandsConsoleCommandTest extends TestCase
{
    /**
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubMasterFactory;

    protected function setUp()
    {
        $this->stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge(get_class_methods(MasterFactory::class), ['createCommandConsumer']))
            ->disableOriginalConstructor()
            ->getMock();
    }
    
    public function testIsAConsoleCommand()
    {
        $command = new ConsumeCommandsConsoleCommand($this->stubMasterFactory);
        $this->assertInstanceOf(ConsoleCommand::class, $command);
    }

    public function testCallsProcessOnTheCommandConsumer()
    {
        $mockCommandConsumer = $this->createMock(CommandConsumer::class);
        $mockCommandConsumer->expects($this->once())->method('processAll');
        $this->stubMasterFactory->method('createCommandConsumer')->willReturn($mockCommandConsumer);
        (new ConsumeCommandsConsoleCommand($this->stubMasterFactory))->run();
    }
}
