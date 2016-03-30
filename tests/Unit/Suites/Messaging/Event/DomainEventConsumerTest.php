<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Event\Exception\UnableToFindDomainEventHandlerException;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\QueueMessageConsumer;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\DomainEventConsumer
 * @uses   \LizardsAndPumpkins\Messaging\Event\Exception\DomainEventHandlerFailedMessage
 * @uses   \LizardsAndPumpkins\Messaging\Event\FailedToReadFromDomainEventQueueMessage
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

    public function testItIsAQueueMessageConsumer()
    {
        $this->assertInstanceOf(QueueMessageConsumer::class, $this->domainEventConsumer);
    }

    public function testItCallsNextIfQueueIsReady()
    {
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->stubQueue->method('next')->willReturn($stubDomainEvent);
        $this->stubQueue->method('isReadyForNext')
            ->willReturnOnConsecutiveCalls(true, true, true, false);

        $stubEventHandler = $this->getMock(DomainEventHandler::class);
        $this->mockLocator->expects($this->exactly(3))
            ->method('getHandlerFor')
            ->willReturn($stubEventHandler);

        $this->domainEventConsumer->process();
    }

    public function testLogEntryIsWrittenIfLocatorIsNotFound()
    {
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->stubQueue->method('next')->willReturn($stubDomainEvent);
        $this->stubQueue->method('isReadyForNext')->willReturnOnConsecutiveCalls(true, false);

        $this->mockLocator->method('getHandlerFor')->willThrowException(new UnableToFindDomainEventHandlerException);
        $this->mockLogger->expects($this->once())->method('log');

        $this->domainEventConsumer->process();
    }

    public function testLogEntryIsWrittenOnQueueReadFailure()
    {
        $this->stubQueue->method('next')->willThrowException(new \UnderflowException);
        $this->stubQueue->method('isReadyForNext')->willReturnOnConsecutiveCalls(true, false);
        $this->mockLogger->expects($this->once())->method('log');

        $this->domainEventConsumer->process();
    }

    public function testConsumerStopsIfProcessingLimitIsReached()
    {
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->stubQueue->method('next')->willReturn($stubDomainEvent);
        $this->stubQueue->method('isReadyForNext')->willReturn(true);

        $stubEventHandler = $this->getMock(DomainEventHandler::class);
        $stubEventHandler->expects($this->exactly(200))->method('process');
        $this->mockLocator->method('getHandlerFor')->willReturn($stubEventHandler);

        $this->domainEventConsumer->process();
    }
}
