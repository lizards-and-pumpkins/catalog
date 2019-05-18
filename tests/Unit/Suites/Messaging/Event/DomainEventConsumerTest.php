<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Event\Exception\UnableToFindDomainEventHandlerException;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Messaging\QueueMessageConsumer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\DomainEventConsumer
 * @uses   \LizardsAndPumpkins\Messaging\Event\Exception\DomainEventHandlerFailedMessage
 * @uses   \LizardsAndPumpkins\Messaging\Event\FailedToReadFromDomainEventQueueMessage
 */
class DomainEventConsumerTest extends TestCase
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
        $this->mockQueue = $this->createMock(Queue::class);
        $this->mockLocator = $this->createMock(DomainEventHandlerLocator::class);
        $this->mockLogger = $this->createMock(Logger::class);

        $this->domainEventConsumer = new DomainEventConsumer($this->mockQueue, $this->mockLocator, $this->mockLogger);
    }

    public function testItIsAQueueMessageConsumer()
    {
        $this->assertInstanceOf(QueueMessageConsumer::class, $this->domainEventConsumer);
    }

    public function testCallsConsumeWithTheNumberOdMessagesOnTheQueue()
    {
        $this->mockQueue->method('count')->willReturnOnConsecutiveCalls(2, 0);
        $this->mockQueue->expects($this->once())->method('consume')->with($this->domainEventConsumer);
        $this->domainEventConsumer->processAll();
    }

    public function testLogEntryIsWrittenOnQueueReadFailure()
    {
        $this->mockQueue->method('count')->willReturnOnConsecutiveCalls(1, 0);
        $this->mockQueue->expects($this->once())->method('consume')->willThrowException(new \UnderflowException);
        $this->mockLogger->expects($this->once())->method('log');

        $this->domainEventConsumer->processAll();
    }
    
    public function testLogEntryIsWrittenOnQueueReadFailureDuringProcessAll()
    {
        $this->mockQueue->method('count')->willReturnOnConsecutiveCalls(1, 0);
        $this->mockQueue->expects($this->once())->method('consume')->willThrowException(new \UnderflowException);
        $this->mockLogger->expects($this->once())->method('log');

        $this->domainEventConsumer->processAll();
    }

    public function testDelegatesProcessingToLocatedEventHandler()
    {
        $mockEventHandler = $this->createMock(DomainEventHandler::class);
        $mockEventHandler->expects($this->once())->method('process');
        $this->mockLocator->method('getHandlerFor')->willReturn($mockEventHandler);

        $this->mockQueue->method('count')->willReturnOnConsecutiveCalls(1, 0);
        $this->mockQueue->method('consume')
            ->willReturnCallback(function (MessageReceiver $messageReceiver) {
                /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubMessage */
                $stubMessage = $this->createMock(Message::class);
                $messageReceiver->receive($stubMessage);
            });

        $this->domainEventConsumer->processAll();
    }

    public function testLogsExceptionIfEventHandlerIsNotFound()
    {
        $this->mockLogger->expects($this->once())->method('log');

        $this->mockQueue->method('count')->willReturnOnConsecutiveCalls(1, 0);
        $this->mockQueue->method('consume')
            ->willReturnCallback(function (MessageReceiver $messageReceiver) {
                /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubMessage */
                $stubMessage = $this->createMock(Message::class);
                $messageReceiver->receive($stubMessage);
            });

        $this->mockLocator->method('getHandlerFor')->willThrowException(new UnableToFindDomainEventHandlerException());

        $this->domainEventConsumer->processAll();
    }
}
