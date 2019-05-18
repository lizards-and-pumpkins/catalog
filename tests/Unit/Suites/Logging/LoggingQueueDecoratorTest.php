<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\MessageReceiver;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Logging\Stub\ClearableStubQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Storage\Clearable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Logging\LoggingQueueDecorator
 * @uses   \LizardsAndPumpkins\Logging\QueueAddLogMessage
 */
class LoggingQueueDecoratorTest extends TestCase
{
    /**
     * @var LoggingQueueDecorator;
     */
    private $decorator;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $decoratedQueue;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @return Message|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockMessage() : Message
    {
        return $this->createMock(Message::class);
    }

    protected function setUp()
    {
        $this->mockLogger = $this->createMock(Logger::class);
        $this->decoratedQueue = $this->createMock(Queue::class);
        $this->decorator = new LoggingQueueDecorator($this->decoratedQueue, $this->mockLogger);
    }

    public function testItImplementsTheQueueInterface()
    {
        $this->assertInstanceOf(Queue::class, $this->decorator);
    }

    public function testItDelegatesCountCallsToTheDecoratedQueue()
    {
        $expected = 42;
        $this->decoratedQueue->expects($this->once())->method('count')->willReturn($expected);
        $this->assertSame($expected, $this->decorator->count());
    }

    public function testItDelegatesAddCallsToTheDecoratedQueue()
    {
        $testMessage = $this->createMockMessage();
        $this->decoratedQueue->expects($this->once())->method('add')->with($testMessage);
        $this->decorator->add($testMessage);
    }

    public function testItDelegatesConsumeCallsToTheDecoratedQueue()
    {
        /** @var MessageReceiver|\PHPUnit_Framework_MockObject_MockObject $stubMessageReceiver */
        $stubMessageReceiver = $this->createMock(MessageReceiver::class);
        $this->decoratedQueue->expects($this->once())->method('consume')->with($stubMessageReceiver);

        $this->decorator->consume($stubMessageReceiver);
    }

    public function testItLogsAddedMessages()
    {
        $testData = $this->createMockMessage();
        $this->mockLogger->expects($this->once())->method('log')->with($this->isInstanceOf(QueueAddLogMessage::class));
        $this->decorator->add($testData);
    }

    public function testItIsClearable()
    {
        $this->assertInstanceOf(Clearable::class, $this->decorator);
    }

    public function testItDelegatesClearCallsToTheDecoratedQueue()
    {
        /** @var ClearableStubQueue|\PHPUnit_Framework_MockObject_MockObject $mockQueue */
        $mockQueue = $this->createMock(ClearableStubQueue::class);
        $mockQueue->expects($this->once())->method('clear');
        (new LoggingQueueDecorator($mockQueue, $this->mockLogger))->clear();
    }
}
