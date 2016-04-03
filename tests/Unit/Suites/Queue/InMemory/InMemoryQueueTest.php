<?php

namespace LizardsAndPumpkins\Queue\InMemory;

use LizardsAndPumpkins\Messaging\Queue\Exception\NotSerializableException;
use LizardsAndPumpkins\Queue\Stub\StubMessage;
use LizardsAndPumpkins\Util\Storage\Clearable;

require_once __DIR__ . '/../Stub/StubMessage.php';

/**
 * @covers \LizardsAndPumpkins\Queue\InMemory\InMemoryQueue
 */
class InMemoryQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryQueue
     */
    private $queue;
    
    public function setUp()
    {
        $this->queue = new InMemoryQueue();
    }

    public function testQueueIsInitiallyEmpty()
    {
        $this->assertCount(0, $this->queue);
    }

    public function testExceptionIsThrownIfNonSerializableDataIsPassed()
    {
        $this->expectException(NotSerializableException::class);
        $simpleXml = simplexml_load_string('<root />');
        $this->queue->add($simpleXml);
    }

    public function testSerializableObjectCnBeAddedToQueue()
    {
        $stubSerializableData = $this->getMock(\Serializable::class);
        $this->queue->add($stubSerializableData);
        $this->assertCount(1, $this->queue);
    }

    public function testItIsNotReadyForNextWhenTheQueueIsEmpty()
    {
        $this->assertFalse($this->queue->isReadyForNext());
    }

    public function testItIsReadyForNextWhenTheQueueIsNotEmpty()
    {
        $this->queue->add('dummy');
        $this->assertTrue($this->queue->isReadyForNext());
    }
    
    public function testNextMessageIsReturned()
    {
        $stubSerializableData = $this->getMock(\Serializable::class);
        $stubSerializableData->expects($this->once())->method('serialize')->willReturn(serialize(''));
        $this->queue->add($stubSerializableData);
        $result = $this->queue->next();

        $this->assertEquals($stubSerializableData, $result);
    }

    public function testReturnedMessageIsRemovedFromQuue()
    {
        $stubSerializableData = $this->getMock(\Serializable::class);
        $stubSerializableData->expects($this->once())->method('serialize')->willReturn(serialize(''));
        $this->queue->add($stubSerializableData);
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
        $this->queue->add(new StubMessage('One'));
        $this->queue->add(new StubMessage('Two'));

        $this->assertEquals('One', $this->queue->next()->serialize());
        $this->assertEquals('Two', $this->queue->next()->serialize());
    }

    public function testItIsClearable()
    {
        $this->assertInstanceOf(Clearable::class, $this->queue);
    }

    public function testItClearsTheQueue()
    {
        $this->queue->add(new StubMessage('One'));
        $this->queue->add(new StubMessage('Two'));
        $this->queue->add(new StubMessage('Three'));
        $this->assertCount(3, $this->queue);
        $this->queue->clear();
        $this->assertCount(0, $this->queue);
    }
}
