<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Queue;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;

/**
 * @covers \LizardsAndPumpkins\Messaging\Queue\EnqueuesMessageEnvelope
 */
class EnqueuesMessageEnvelopeTest extends \PHPUnit\Framework\TestCase
{
    public function testCanBeCreatedWithACommandQueue()
    {
        $instance = EnqueuesMessageEnvelope::fromCommandQueue($this->createMock(CommandQueue::class));
        $this->assertInstanceOf(EnqueuesMessageEnvelope::class, $instance);
    }
    
    public function testCanBeCreatedWithADomainEventQueue()
    {
        $instance = EnqueuesMessageEnvelope::fromDomainEventQueue($this->createMock(DomainEventQueue::class));
        $this->assertInstanceOf(EnqueuesMessageEnvelope::class, $instance);
    }

    public function testAddsCommandsToQueue()
    {
        $dummyMessage = $this->createMock(Command::class);
        $mockQueue = $this->createMock(CommandQueue::class);
        $mockQueue->expects($this->once())->method('add')->with($dummyMessage);
        EnqueuesMessageEnvelope::fromCommandQueue($mockQueue)->add($dummyMessage);
    }

    public function testAddsEventsToQueue()
    {
        $dummyMessage = $this->createMock(DomainEvent::class);
        $mockQueue = $this->createMock(DomainEventQueue::class);
        $mockQueue->expects($this->once())->method('add')->with($dummyMessage);
        EnqueuesMessageEnvelope::fromDomainEventQueue($mockQueue)->add($dummyMessage);
    }
}
