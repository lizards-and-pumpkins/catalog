<?php

namespace Brera\PoC\Queue;

use Brera\PoC\DomainEvent;

/**
 * @package \Brera\PoC
 * @covers \Brera\PoC\Queue\InMemoryDomainEventQueue
 */
class InMemoryDomainEventQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InMemoryDomainEventQueue
     */
    private $queue;
    
    public function setUp()
    {
        $this->queue = new InMemoryDomainEventQueue();
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
     */
    public function itCanAddADomainEventToTheQueue()
    {
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->queue->add($stubDomainEvent);
        $this->assertCount(1, $this->queue);
    }

    /**
     * @test
     */
    public function itShouldReturnTheNextEventFromTheQueue()
    {
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->queue->add($stubDomainEvent);
        $result = $this->queue->next();
        $this->assertEquals($stubDomainEvent, $result);
    }

    /**
     * @test
     */
    public function retrievingTheEventShouldRemoveItFromTheQueue()
    {
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->queue->add($stubDomainEvent);
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
