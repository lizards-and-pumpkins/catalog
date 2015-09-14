<?php


namespace LizardsAndPumpkins\Queue;

use LizardsAndPumpkins\Log\Logger;
use LizardsAndPumpkins\Queue\Stub\ClearableStubQueue;
use LizardsAndPumpkins\Utils\Clearable;

/**
 * @covers \LizardsAndPumpkins\Queue\LoggingQueueDecorator
 * @uses   \LizardsAndPumpkins\Queue\QueueAddLogMessage
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

    protected function setUp()
    {
        $this->mockLogger = $this->getMock(Logger::class);
        $this->decoratedQueue = $this->getMock(Queue::class);
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
        $testData = new \stdClass();
        $this->decoratedQueue->expects($this->once())->method('add')->with($testData);
        $this->decorator->add($testData);
    }

    public function testItDelegatesNextCallsToTheDecoratedQueue()
    {
        $expected = new \stdClass();
        $this->decoratedQueue->expects($this->once())->method('next')->willReturn($expected);
        $this->assertSame($expected, $this->decorator->next());
    }

    public function testItLoggsAddedMessages()
    {
        $testData = new \stdClass();
        $this->mockLogger->expects($this->once())->method('log')->with($this->isInstanceOf(QueueAddLogMessage::class));
        $this->decorator->add($testData);
    }

    public function testItIsClearable()
    {
        $this->assertInstanceOf(Clearable::class, $this->decorator);
    }

    public function testItDelegatesClearCallsToTheDecoratedQueue()
    {
        $mockQueue = $this->getMock(ClearableStubQueue::class);
        $mockQueue->expects($this->once())->method('clear');
        (new LoggingQueueDecorator($mockQueue, $this->mockLogger))->clear();
    }
}
