<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Event\Exception\InvalidDomainEventConsumerPidException;
use LizardsAndPumpkins\Messaging\Event\Exception\NoShutdownWorkerDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\ShutdownWorkerDomainEvent
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class ShutdownWorkerDomainEventTest extends \PHPUnit\Framework\TestCase
{
    public function testImplementsDomainEventInterface()
    {
        $this->assertInstanceOf(DomainEvent::class, new ShutdownWorkerDomainEvent('*'));
    }

    /**
     * @dataProvider invalidConsumerPidProvider
     */
    public function testThrowsExceptionForInvalidConsumerPid($invalidConsumerPid)
    {
        $this->expectException(InvalidDomainEventConsumerPidException::class);
        $msg = sprintf('The event consumer PID has to be numeric or "*" for all, got "%s"', $invalidConsumerPid);
        $this->expectExceptionMessage($msg);
        new ShutdownWorkerDomainEvent($invalidConsumerPid);
    }

    public function invalidConsumerPidProvider(): array
    {
        return [
            [''],
            ['a'],
            ['_'],
            ['.'],
            [' 1'],
            ['1 '],
            ['0'],
        ];
    }

    public function testReturnsTheSpecifiedConsumerPid()
    {
        $this->assertSame('555', (new ShutdownWorkerDomainEvent('555'))->getPid());
        $this->assertSame('234', (new ShutdownWorkerDomainEvent('234'))->getPid());
    }

    public function testReturnsAMessageWithTheRightNameAndPayload()
    {
        $message = (new ShutdownWorkerDomainEvent('*'))->toMessage();
        $this->assertSame('shutdown_worker', $message->getName());
        $this->assertSame('*', $message->getPayload()['pid']);
    }

    public function testReturnsMessageWithSpecifiedRetryCount()
    {
        $this->assertSame(0, (new ShutdownWorkerDomainEvent('123'))->toMessage()->getPayload()['retry_count']);
        $this->assertSame(1, (new ShutdownWorkerDomainEvent('123', 1))->toMessage()->getPayload()['retry_count']);
        $this->assertSame(2, (new ShutdownWorkerDomainEvent('123', 2))->toMessage()->getPayload()['retry_count']);
    }

    public function testCanBeRehydratedFromMessage()
    {
        $message = (new ShutdownWorkerDomainEvent('*', 42))->toMessage();
        $command = ShutdownWorkerDomainEvent::fromMessage($message);
        $this->assertInstanceOf(ShutdownWorkerDomainEvent::class, $command);
        $this->assertSame('*', $command->getPid());
        $this->assertSame(42, $command->getRetryCount());
    }



    public function testThrowsExceptionIfMessageCodeDoesNotMatchShutdownWorkerCode()
    {
        $this->expectException(NoShutdownWorkerDomainEventMessageException::class);
        $message = 'Unable to rehydrate event from "foo" queue message, expected "shutdown_worker"';
        $this->expectExceptionMessage($message);

        ShutdownWorkerDomainEvent::fromMessage(Message::withCurrentTime('foo', [], []));
    }
}
