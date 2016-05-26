<?php

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Util\Storage\Clearable;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\InMemoryQueue
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class InMemoryQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryQueue
     */
    private $queue;

    /**
     * @var Message
     */
    private $testMessage;

    public function setUp()
    {
        $this->testMessage = Message::withCurrentTime('foo', 'bar', []);
        $this->queue = new InMemoryQueue();
    }

    public function testQueueIsInitiallyEmpty()
    {
        $this->assertCount(0, $this->queue);
    }

    public function testItIsNotReadyForNextWhenTheQueueIsEmpty()
    {
        $this->assertFalse($this->queue->isReadyForNext());
    }

    public function testItIsReadyForNextWhenTheQueueIsNotEmpty()
    {
        $this->queue->add($this->testMessage);
        $this->assertTrue($this->queue->isReadyForNext());
    }

    public function testNextMessageIsReturned()
    {
        $this->queue->add($this->testMessage);
        $result = $this->queue->next();

        $this->assertEquals($this->testMessage, $result);
    }

    public function testReturnedMessageIsRemovedFromQuue()
    {
        $this->queue->add($this->testMessage);
        $this->queue->next();

        $this->assertCount(0, $this->queue);
    }

    public function testExceptionIsThrownDuringAttemptToReceiveMessageFromEmptyQueue()
    {
        $this->expectException(\RuntimeException::class);
        $this->queue->next();
    }

    public function testItReturnsTheMessagesInTheRightOrder()
    {
        $this->queue->add(Message::withCurrentTime('One', '', []));
        $this->queue->add(Message::withCurrentTime('Two', '', []));

        $this->assertEquals('One', $this->queue->next()->getName());
        $this->assertEquals('Two', $this->queue->next()->getName());
    }

    public function testItIsClearable()
    {
        $this->assertInstanceOf(Clearable::class, $this->queue);
    }

    public function testItClearsTheQueue()
    {
        $this->queue->add(Message::withCurrentTime('One', '', []));
        $this->queue->add(Message::withCurrentTime('Two', '', []));
        $this->queue->add(Message::withCurrentTime('Three', '', []));
        $this->assertCount(3, $this->queue);
        $this->queue->clear();
        $this->assertCount(0, $this->queue);
    }
}
