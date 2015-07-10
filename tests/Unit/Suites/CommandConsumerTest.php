<?php

namespace Brera;

use Brera\Queue\Queue;

/**
 * @covers \Brera\CommandConsumer
 * @uses   \Brera\CommandHandlerFailedMessage
 * @uses   \Brera\FailedToReadFromCommandQueueMessage
 */
class CommandConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockQueue;

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
        $this->mockQueue = $this->getMock(Queue::class);
        $this->mockLocator = $this->getMock(CommandHandlerLocator::class, [], [], '', false);
        $this->mockLogger = $this->getMock(Logger::class);

        $this->commandConsumer = new CommandConsumer($this->mockQueue, $this->mockLocator, $this->mockLogger);
    }

    /**
     * @dataProvider getNumberOfCommandsToProcess
     * @param int $numberOfCommandsToProcess
     */
    public function testCommandHandlerIsTriggeredForSetNumberOfCommands($numberOfCommandsToProcess)
    {
        $stubCommand = $this->getMock(Command::class);
        $this->mockQueue->method('next')
            ->willReturn($stubCommand);

        $mockCommandHandler = $this->getMock(CommandHandler::class);
        $mockCommandHandler->expects($this->exactly($numberOfCommandsToProcess))
            ->method('process');

        $this->mockLocator->method('getHandlerFor')
            ->willReturn($mockCommandHandler);

        $this->commandConsumer->process($numberOfCommandsToProcess);
    }

    /**
     * @return array[]
     */
    public function getNumberOfCommandsToProcess()
    {
        return [[1], [2], [3]];
    }

    public function testLogEntryIsWrittenIfLocatorIsNotFound()
    {
        $numberOfCommandsToProcess = 1;

        $stubCommand = $this->getMock(Command::class);
        $this->mockQueue->method('next')
            ->willReturn($stubCommand);

        $exception = $this->getMock(UnableToFindDomainEventHandlerException::class);
        $this->mockLocator->expects($this->exactly($numberOfCommandsToProcess))
            ->method('getHandlerFor')
            ->willThrowException($exception);

        $this->mockLogger->expects($this->exactly($numberOfCommandsToProcess))
            ->method('log');

        $this->commandConsumer->process($numberOfCommandsToProcess);
    }

    public function testLogEntryIsWrittenOnQueueReadFailure()
    {
        $numberOfCommandsToProcess = 1;

        $stubUnderflowException = $this->getMock(\UnderflowException::class);
        $this->mockQueue->expects($this->exactly($numberOfCommandsToProcess))
            ->method('next')
            ->willThrowException($stubUnderflowException);

        $this->mockLogger->expects($this->exactly($numberOfCommandsToProcess))
            ->method('log');

        $this->commandConsumer->process($numberOfCommandsToProcess);
    }
}
