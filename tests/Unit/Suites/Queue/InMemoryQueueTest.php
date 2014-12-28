<?php

namespace Brera\PoC\Queue;

/**
 * @covers \Brera\PoC\Queue\InMemoryQueue
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

    /**
     * @test
     */
    public function itShouldInitiallyBeEmpty()
    {
        $this->assertCount(0, $this->queue);
    }

	/**
	 * @test
	 * @expectedException \Brera\PoC\Queue\NotSerializableException
	 */
	public function itShouldThrowNotSerializableException()
	{
		$this->queue->add('foo');
	}

    /**
     * @test
     */
    public function itCanAddSerializableObjectToTheQueue()
    {
        $stubSerializableData = $this->getMock(\Serializable::class);
        $this->queue->add($stubSerializableData);
        $this->assertCount(1, $this->queue);
    }

    /**
     * @test
     */
    public function itShouldReturnTheNextEventFromTheQueue()
    {
	    $stubSerializableData = $this->getMock(\Serializable::class);
	    $stubSerializableData->expects($this->once())
		    ->method('serialize')
		    ->willReturn(serialize(''));
        $this->queue->add($stubSerializableData);
        $result = $this->queue->next();
        $this->assertEquals($stubSerializableData, $result);
    }

    /**
     * @test
     */
    public function retrievingTheEventShouldRemoveItFromTheQueue()
    {
	    $stubSerializableData = $this->getMock(\Serializable::class);
	    $stubSerializableData->expects($this->once())
	                         ->method('serialize')
	                         ->willReturn(serialize(''));
        $this->queue->add($stubSerializableData);
        $this->queue->next();
        $this->assertCount(0, $this->queue);
    }
    
    /**
     * @test
     * @expectedException \RuntimeException 
     */
    public function itShouldThrowAnExceptionIfNextIsCalledOnAnEmptyQueue()
    {
        $this->queue->next();
    }
    
    /* TODO: test it should return the events in the correct order */
} 
