<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Event\Exception\UnableToFindDomainEventHandlerException;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
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
        $this->stubQueue = $this->createMock(Queue::class);
        $this->mockLocator = $this->createMock(DomainEventHandlerLocator::class);
        $this->mockLogger = $this->createMock(Logger::class);

        $this->domainEventConsumer = new DomainEventConsumer($this->stubQueue, $this->mockLocator, $this->mockLogger);
    }

    public function testItIsAQueueMessageConsumer()
    {
        $this->assertInstanceOf(QueueMessageConsumer::class, $this->domainEventConsumer);
    }

    public function testConsumesMessagesFromQueue()
    {
        $this->stubQueue->expects($this->once())->method('consume')->with($this->domainEventConsumer);

        $this->domainEventConsumer->process();
    }

    public function testLogEntryIsWrittenOnQueueReadFailure()
    {
        $this->stubQueue->expects($this->once())->method('consume')->willThrowException(new \UnderflowException);
        $this->mockLogger->expects($this->once())->method('log');

        $this->domainEventConsumer->process();
    }

    public function testDelegatesProcessingToLocatedEventHandler()
    {
        $mockEventHandler = $this->createMock(DomainEventHandler::class);
        $mockEventHandler->expects($this->once())->method('process');
        $this->mockLocator->method('getHandlerFor')->willReturn($mockEventHandler);

        $this->stubQueue->method('consume')
            ->willReturnCallback(function (MessageReceiver $messageReceiver) {
                /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubMessage */
                $stubMessage = $this->createMock(Message::class);
                $messageReceiver->receive($stubMessage);
            });

        $this->domainEventConsumer->process();
    }

    public function testLogsExceptionIfEventHandlerIsNotFound()
    {
        $this->mockLogger->expects($this->once())->method('log');

        $this->stubQueue->method('consume')
            ->willReturnCallback(function (MessageReceiver $messageReceiver) {
                /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubMessage */
                $stubMessage = $this->createMock(Message::class);
                $messageReceiver->receive($stubMessage);
            });

        $this->mockLocator->method('getHandlerFor')->willThrowException(new UnableToFindDomainEventHandlerException());

        $this->domainEventConsumer->process();
    }
}
