<?php

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Logging\Stub\ClearableStubQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Storage\Clearable;

/**
 * @covers \LizardsAndPumpkins\Logging\LoggingQueueDecorator
 * @uses   \LizardsAndPumpkins\Logging\QueueAddLogMessage
 */
class LoggingQueueDecoratorTest extends \PHPUnit_Framework_TestCase
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
     * @return Message
     */
    private function createMockMessage()
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

    public function testItDelegatesTheIsReadyForNextCallToTheDecoratedQueue()
    {
        $expected = true;
        $this->decoratedQueue->expects($this->once())->method('isReadyForNext')->willReturn($expected);
        $this->assertSame($expected, $this->decorator->isReadyForNext());
    }

    public function testItDelegatesAddCallsToTheDecoratedQueue()
    {
        $testMessage = $this->createMockMessage();
        $this->decoratedQueue->expects($this->once())->method('add')->with($testMessage);
        $this->decorator->add($testMessage);
    }

    public function testItDelegatesNextCallsToTheDecoratedQueue()
    {
        $expected = $this->createMockMessage();
        $this->decoratedQueue->expects($this->once())->method('next')->willReturn($expected);
        $this->assertSame($expected, $this->decorator->next());
    }

    public function testItLoggsAddedMessages()
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
        $mockQueue = $this->createMock(ClearableStubQueue::class);
        $mockQueue->expects($this->once())->method('clear');
        (new LoggingQueueDecorator($mockQueue, $this->mockLogger))->clear();
    }
}
