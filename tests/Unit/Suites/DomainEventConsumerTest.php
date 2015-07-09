<?php

namespace Brera;

use Brera\Queue\Queue;

/**
 * @covers \Brera\DomainEventConsumer
 * @uses   \Brera\DomainEventHandlerFailedMessage
 * @uses   \Brera\FailedToReadFromDomainEventQueueMessage
 */
class DomainEventConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomainEventConsumer
     */
    private $domainEventConsumer;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockQueue;

    /**
     * @var DomainEventHandlerLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLocator;

    protected function setUp()
    {
        $this->mockQueue = $this->getMock(Queue::class);
        $this->mockLocator = $this->getMock(DomainEventHandlerLocator::class, [], [], '', false);
        $this->mockLogger = $this->getMock(Logger::class);

        $this->domainEventConsumer = new DomainEventConsumer($this->mockQueue, $this->mockLocator, $this->mockLogger);
    }

    /**
     * @dataProvider getNumberOfEventsToProcess
     * @param int $numberOfEventsToProcess
     */
    public function testDomainEventHandlerIsTriggeredForSetNumberOfEventsEvent($numberOfEventsToProcess)
    {
        $this->addNextMethodToStubDomainEventQueue();

        $stubEventHandler = $this->getMock(DomainEventHandler::class);
        $this->mockLocator->expects($this->exactly($numberOfEventsToProcess))
            ->method('getHandlerFor')
            ->willReturn($stubEventHandler);

        $this->domainEventConsumer->process($numberOfEventsToProcess);
    }

    /**
     * @return array[]
     */
    public function getNumberOfEventsToProcess()
    {
        return array_map(function ($i) {
            return [$i];
        }, range(1, 3));
    }

    public function testLogEntryIsWrittenIfLocatorIsNotFound()
    {
        $numberOfEventsToProcess = 1;

        $this->addNextMethodToStubDomainEventQueue();
        /* @var $exception UnableToFindDomainEventHandlerException|\PHPUnit_Framework_MockObject_MockObject */
        $exception = $this->getMock(UnableToFindDomainEventHandlerException::class);
        $this->mockLocator->expects($this->exactly($numberOfEventsToProcess))
            ->method('getHandlerFor')
            ->willThrowException($exception);

        $this->mockLogger->expects($this->exactly($numberOfEventsToProcess))
            ->method('log');

        $this->domainEventConsumer->process($numberOfEventsToProcess);
    }

    public function testLogEntryIsWrittenOnQueueReadFailure()
    {
        $numberOfEventsToProcess = 1;
        /* @var $stubUnderflowException \UnderflowException|\PHPUnit_Framework_MockObject_MockObject */
        $stubUnderflowException = $this->getMock(\UnderflowException::class);
        $this->mockQueue->expects($this->exactly($numberOfEventsToProcess))
            ->method('next')
            ->willThrowException($stubUnderflowException);

        $this->mockLogger->expects($this->exactly($numberOfEventsToProcess))
            ->method('log');

        $this->domainEventConsumer->process($numberOfEventsToProcess);
    }

    private function addNextMethodToStubDomainEventQueue()
    {
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->mockQueue->expects($this->any())
            ->method('next')
            ->willReturn($stubDomainEvent);
    }
}
