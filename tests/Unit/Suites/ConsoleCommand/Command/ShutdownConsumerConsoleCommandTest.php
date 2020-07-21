<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\Argument\Manager as CliMateArgumentManager;
use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\BaseCliCommand;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirective;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\ShutdownConsumerConsoleCommand
 * @uses   \LizardsAndPumpkins\ConsoleCommand\BaseCliCommand
 * @uses   \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirective
 */
class ShutdownConsumerConsoleCommandTest extends TestCase
{
    /**
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubMasterFactory;

    /**
     * @var CLImate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCliMate;

    private function getCommandArgumentMap($overRideDefaults = []): array
    {
        $arguments = [
            'quiet' => ['quiet', false],
            'type'  => ['type', null],
            'pid'   => ['pid', null],
            'help'  => ['help', null],
        ];
        foreach ($overRideDefaults as $name => $value) {
            $arguments[$name] = [$name, $value];
        }

        return array_values($arguments);
    }

    protected function setUp()
    {
        $this->stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge(get_class_methods(MasterFactory::class), ['getCommandQueue', 'getEventQueue']))
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockCliMate = $this->getMockBuilder(CLImate::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(CLImate::class), ['error', 'output']))
            ->getMock();
        $this->mockCliMate->arguments = $this->createMock(CliMateArgumentManager::class);
    }

    public function testIsABaseCliCommand()
    {
        $command = new ShutdownConsumerConsoleCommand($this->stubMasterFactory, $this->mockCliMate);
        $this->assertInstanceOf(BaseCliCommand::class, $command);
    }

    /**
     * @dataProvider queueTypeDataProvider
     */
    public function testAddsShutdownDirectiveToQueue(string $type, string $queueClass, string $queueFactoryMethod)
    {
        $argumentMap = $this->getCommandArgumentMap([
            'type' => $type,
            'pid'  => 123,
        ]);
        $this->mockCliMate->arguments->method('get')->willReturnMap($argumentMap);
        $this->mockCliMate->expects($this->never())->method('error');
        $mockQueue = $this->createMock($queueClass);
        $mockQueue->expects($this->once())->method('add')->with($this->isInstanceOf(ShutdownWorkerDirective::class));
        $this->stubMasterFactory->method($queueFactoryMethod)->willReturn($mockQueue);

        (new ShutdownConsumerConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }

    public function queueTypeDataProvider(): array
    {
        return [
            [ShutdownConsumerConsoleCommand::COMMAND_CONSUMER, CommandQueue::class, 'getCommandQueue'],
            [ShutdownConsumerConsoleCommand::EVENT_CONSUMER, DomainEventQueue::class, 'getEventQueue'],
        ];
    }

    public function testDisplaysAnErrorIfTheQueueTypeIsNotSpecified()
    {
        $argumentMap = $this->getCommandArgumentMap(['pid' => 123]);
        $this->mockCliMate->arguments->method('get')->willReturnMap($argumentMap);
        $this->mockCliMate->expects($this->atLeastOnce())->method('error')
            ->willReturnCallback(function(string $output) {
                if (strlen($output) <= 3) {
                    $this->fail('Error message is not long enough');
                }
            });

        (new ShutdownConsumerConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }
}
