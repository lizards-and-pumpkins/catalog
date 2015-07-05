<?php

namespace Brera;

use Brera\Queue\Queue;

/**
 * @covers \Brera\DomainCommandConsumer
 * @uses   \Brera\DomainCommandHandlerFailedMessage
 * @uses   \Brera\FailedToReadFromDomainCommandQueueMessage
 */
class DomainCommandConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockQueue;

    /**
     * @var DomainCommandHandlerLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLocator;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @var DomainCommandConsumer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commandConsumer;

    protected function setUp()
    {
        $this->mockQueue = $this->getMock(Queue::class);
        $this->mockLocator = $this->getMock(DomainCommandHandlerLocator::class, [], [], '', false);
        $this->mockLogger = $this->getMock(Logger::class);

        $this->commandConsumer = new DomainCommandConsumer($this->mockQueue, $this->mockLocator, $this->mockLogger);
    }

    public function testDomainCommandHandlerIsTriggeredForSetNumberOfCommands()
    {
        $numberOfCommandsToProcess = rand(1, 10);

        $stubDomainCommand = $this->getMock(DomainCommand::class);
        $this->mockQueue->expects($this->any())
            ->method('next')
            ->willReturn($stubDomainCommand);

        $mockDomainCommandHandler = $this->getMock(DomainCommandHandler::class);
        $mockDomainCommandHandler->expects($this->exactly($numberOfCommandsToProcess))
            ->method('process');

        $this->mockLocator->expects($this->any())
            ->method('getHandlerFor')
            ->willReturn($mockDomainCommandHandler);

        $this->commandConsumer->process($numberOfCommandsToProcess);
    }

    public function testLogEntryIsWrittenIfLocatorIsNotFound()
    {
        $numberOfCommandsToProcess = 1;

        $stubDomainCommand = $this->getMock(DomainCommand::class);
        $this->mockQueue->expects($this->any())
            ->method('next')
            ->willReturn($stubDomainCommand);

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
