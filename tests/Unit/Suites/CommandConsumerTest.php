<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Queue\Queue;

/**
 * @covers \LizardsAndPumpkins\CommandConsumer
 * @uses   \LizardsAndPumpkins\CommandHandlerFailedMessage
 * @uses   \LizardsAndPumpkins\FailedToReadFromCommandQueueMessage
 */
class CommandConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubQueue;

    /**
     * @var CommandHandlerLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLocator;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @var CommandConsumer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandConsumer;

    protected function setUp()
    {
        $this->stubQueue = $this->getMock(Queue::class);
        $this->mockLocator = $this->getMock(CommandHandlerLocator::class, [], [], '', false);
        $this->mockLogger = $this->getMock(Logger::class);

        $this->commandConsumer = new CommandConsumer($this->stubQueue, $this->mockLocator, $this->mockLogger);
    }

    public function testItCallsNextIfQueueIsReady()
    {
        $stubCommand = $this->getMock(Command::class);
        $this->stubQueue->method('next')->willReturn($stubCommand);
        $this->stubQueue->method('isReadyForNext')
            ->willReturnOnConsecutiveCalls(true, true, false);

        $mockCommandHandler = $this->getMock(CommandHandler::class);
        $this->mockLocator->expects($this->exactly(2))->method('getHandlerFor')
            ->willReturn($mockCommandHandler);

        $this->commandConsumer->process();
    }

    public function testLogEntryIsWrittenIfLocatorIsNotFound()
    {
        $stubCommand = $this->getMock(Command::class);
        $this->stubQueue->method('next')->willReturn($stubCommand);
        $this->stubQueue->method('isReadyForNext')->willReturnOnConsecutiveCalls(true, false);

        $this->mockLocator->method('getHandlerFor')->willThrowException(new UnableToFindCommandHandlerException);
        $this->mockLogger->expects($this->once())->method('log');

        $this->commandConsumer->process();
    }

    public function testLogEntryIsWrittenOnQueueReadFailure()
    {
        $this->stubQueue->expects($this->once())->method('next')->willThrowException(new \UnderflowException);
        $this->stubQueue->method('isReadyForNext')->willReturnOnConsecutiveCalls(true, false);
        $this->mockLogger->expects($this->once())->method('log');

        $this->commandConsumer->process();
    }

    public function testConsumerStopsIfProcessingLimitIsReached()
    {
        $stubCommand = $this->getMock(Command::class);
        $this->stubQueue->method('next')->willReturn($stubCommand);
        $this->stubQueue->method('isReadyForNext')->willReturn(true);

        $stubCommandHandler = $this->getMock(CommandHandler::class);
        $stubCommandHandler->expects($this->exactly(200))->method('process');
        $this->mockLocator->method('getHandlerFor')->willReturn($stubCommandHandler);

        $this->commandConsumer->process();
    }
}
