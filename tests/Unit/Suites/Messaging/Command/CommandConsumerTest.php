<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Command\Exception\UnableToFindCommandHandlerException;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Messaging\QueueMessageConsumer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\CommandConsumer
 * @uses   \LizardsAndPumpkins\Messaging\Command\CommandHandlerFailedMessage
 * @uses   \LizardsAndPumpkins\Messaging\Command\FailedToReadFromCommandQueueMessage
 */
class CommandConsumerTest extends TestCase
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
        $this->mockQueue = $this->createMock(Queue::class);
        $this->mockLocator = $this->createMock(CommandHandlerLocator::class);
        $this->mockLogger = $this->createMock(Logger::class);

        $this->commandConsumer = new CommandConsumer($this->mockQueue, $this->mockLocator, $this->mockLogger);
    }

    public function testItIsAQueueMessageConsumer()
    {
        $this->assertInstanceOf(QueueMessageConsumer::class, $this->commandConsumer);
    }

    public function testConsumesMessagesFromQueue()
    {
        $this->mockQueue->expects($this->once())->method('consume')->with($this->commandConsumer);

        $this->commandConsumer->process();
    }
    
    public function testCallsConsumeWithTheNumberOdMessagesOnTheQueue()
    {
        $this->mockQueue->method('count')->willReturnOnConsecutiveCalls(2, 0);
        $this->mockQueue->expects($this->once())->method('consume')->with($this->commandConsumer, 2);
        $this->commandConsumer->processAll();
    }

    public function testLogEntryIsWrittenOnQueueReadFailure()
    {
        $this->mockQueue->expects($this->once())->method('consume')->willThrowException(new \UnderflowException);
        $this->mockLogger->expects($this->once())->method('log');

        $this->commandConsumer->process();
    }

    public function testLogEntryIsWrittenOnQueueReadFailureDuringProcessAll()
    {
        $this->mockQueue->method('count')->willReturnOnConsecutiveCalls(1, 0);
        $this->mockQueue->method('consume')->willThrowException(new \UnderflowException);
        $this->mockLogger->expects($this->once())->method('log');

        $this->commandConsumer->processAll();
    }

    public function testDelegatesProcessingToLocatedCommandHandler()
    {
        $mockCommandHandler = $this->createMock(CommandHandler::class);
        $mockCommandHandler->expects($this->once())->method('process');
        $this->mockLocator->method('getHandlerFor')->willReturn($mockCommandHandler);

        $this->mockQueue->method('consume')
            ->willReturnCallback(function (MessageReceiver $messageReceiver) {
                /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubMessage */
                $stubMessage = $this->createMock(Message::class);
                $messageReceiver->receive($stubMessage);
            });
        
        $this->commandConsumer->process();
    }

    public function testLogsExceptionIfCommandHandlerIsNotFound()
    {
        $this->mockLogger->expects($this->once())->method('log');

        $this->mockQueue->method('consume')
            ->willReturnCallback(function (MessageReceiver $messageReceiver) {
                /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubMessage */
                $stubMessage = $this->createMock(Message::class);
                $messageReceiver->receive($stubMessage);
            });

        $this->mockLocator->method('getHandlerFor')->willThrowException(new UnableToFindCommandHandlerException);

        $this->commandConsumer->process();
    }
}
