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
    private $stubQueue;

    /**
     * @var DomainEventHandlerLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLocator;

    protected function setUp()
    {
        $this->stubQueue = $this->getMock(Queue::class);
        $this->mockLocator = $this->getMock(DomainEventHandlerLocator::class, [], [], '', false);
        $this->mockLogger = $this->getMock(Logger::class);

        $this->domainEventConsumer = new DomainEventConsumer($this->stubQueue, $this->mockLocator, $this->mockLogger);
    }

    /**
     * @dataProvider getNumberOfEventsInQueue
     * @param int $numberOfEventsInQueue
     */
    public function testAllEventsInQueueAreProcessed($numberOfEventsInQueue)
    {
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->stubQueue->method('next')->willReturn($stubDomainEvent);
        $this->stubQueue->method('count')
            ->will(call_user_func_array([$this, 'onConsecutiveCalls'], range($numberOfEventsInQueue, 0)));

        $stubEventHandler = $this->getMock(DomainEventHandler::class);
        $this->mockLocator->expects($this->exactly($numberOfEventsInQueue))
            ->method('getHandlerFor')
            ->willReturn($stubEventHandler);

        $this->domainEventConsumer->process();
    }

    /**
     * @return array[]
     */
    public function getNumberOfEventsInQueue()
    {
        return [[1], [2], [3]];
    }

    public function testLogEntryIsWrittenIfLocatorIsNotFound()
    {
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->stubQueue->method('next')->willReturn($stubDomainEvent);
        $this->stubQueue->method('count')->willReturnOnConsecutiveCalls(1, 0);

        $this->mockLocator->method('getHandlerFor')->willThrowException(new UnableToFindDomainEventHandlerException);
        $this->mockLogger->expects($this->once())->method('log');

        $this->domainEventConsumer->process();
    }

    public function testLogEntryIsWrittenOnQueueReadFailure()
    {
        $this->stubQueue->method('next')->willThrowException(new \UnderflowException);
        $this->stubQueue->method('count')->willReturnOnConsecutiveCalls(1, 0);
        $this->mockLogger->expects($this->once())->method('log');

        $this->domainEventConsumer->process();
    }

    public function testConsumerStopsIfProcessingLimitIsReached()
    {
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->stubQueue->method('next')->willReturn($stubDomainEvent);
        $this->stubQueue->method('count')->willReturn(1);

        $stubEventHandler = $this->getMock(DomainEventHandler::class);
        $stubEventHandler->expects($this->exactly(200))->method('process');
        $this->mockLocator->method('getHandlerFor')->willReturn($stubEventHandler);

        $this->domainEventConsumer->process();
    }
}
