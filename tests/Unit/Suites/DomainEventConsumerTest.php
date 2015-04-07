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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $stubLogger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $stubQueue;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $stubLocator;

    protected function setUp()
    {
        $this->stubQueue = $this->getMock(Queue::class);
        $this->stubLocator = $this->getMockBuilder(DomainEventHandlerLocator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubLogger = $this->getMock(Logger::class);

        $this->domainEventConsumer = new DomainEventConsumer($this->stubQueue, $this->stubLocator, $this->stubLogger);
    }

    /**
     * @test
     */
    public function itShouldCallProcessMethodOfDomainEventHandler()
    {
        $numberOfEventsToProcess = 1;

        $this->addNextMethodToStubDomainEventQueue($numberOfEventsToProcess);

        $stubEventHandler = $this->getMock(DomainEventHandler::class);
        $this->stubLocator->expects($this->exactly($numberOfEventsToProcess))
            ->method('getHandlerFor')
            ->willReturn($stubEventHandler);

        $this->domainEventConsumer->process($numberOfEventsToProcess);
    }

    /**
     * @test
     */
    public function itShouldWriteLogEntryIfLocatorIsNotFound()
    {
        $numberOfEventsToProcess = 1;

        $this->addNextMethodToStubDomainEventQueue($numberOfEventsToProcess);
        /* @var $exception UnableToFindDomainEventHandlerException|\PHPUnit_Framework_MockObject_MockObject */
        $exception = $this->getMock(UnableToFindDomainEventHandlerException::class);
        $this->stubLocator->expects($this->exactly($numberOfEventsToProcess))
            ->method('getHandlerFor')
            ->willThrowException($exception);

        $this->stubLogger->expects($this->exactly($numberOfEventsToProcess))
            ->method('log');

        $this->domainEventConsumer->process($numberOfEventsToProcess);
    }

    /**
     * @test
     */
    public function itShouldWriteLogEntryOnQueueReadFailure()
    {
        $numberOfEventsToProcess = 1;
        /* @var $stubUnderflowException \UnderflowException|\PHPUnit_Framework_MockObject_MockObject */
        $stubUnderflowException = $this->getMock(\UnderflowException::class);
        $this->stubQueue->expects($this->exactly($numberOfEventsToProcess))
            ->method('next')
            ->willThrowException($stubUnderflowException);

        $this->stubLogger->expects($this->exactly($numberOfEventsToProcess))
            ->method('log');

        $this->domainEventConsumer->process($numberOfEventsToProcess);
    }

    /**
     * @param int $numberOfEventsToProcess
     */
    private function addNextMethodToStubDomainEventQueue($numberOfEventsToProcess)
    {
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->stubQueue->expects($this->exactly($numberOfEventsToProcess))
            ->method('next')
            ->willReturn($stubDomainEvent);
    }
}
