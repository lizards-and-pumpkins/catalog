<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Consumer;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Consumer\Exception\InvalidMessageConsumerPidException;
use LizardsAndPumpkins\Messaging\Consumer\Exception\NotShutdownWorkerDirectiveMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirective
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class ShutdownWorkerDirectiveTest extends \PHPUnit\Framework\TestCase
{
    public function testImplementsCommandAndEventInterface()
    {
        $this->assertInstanceOf(Command::class, new ShutdownWorkerDirective('*'));
        $this->assertInstanceOf(DomainEvent::class, new ShutdownWorkerDirective('*'));
    }
    
    public function testReturnsMessageWithShutdownWorkerNameAndPayload()
    {
        $message = (new ShutdownWorkerDirective('123'))->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(ShutdownWorkerDirective::CODE, $message->getName());
        $this->assertSame('123', $message->getPayload()['pid']);
    }

    public function testReturnsMessageWithSpecifiedRetryCount()
    {
        $this->assertSame(0, (new ShutdownWorkerDirective('123'))->toMessage()->getPayload()['retry_count']);
        $this->assertSame(1, (new ShutdownWorkerDirective('123', 1))->toMessage()->getPayload()['retry_count']);
        $this->assertSame(2, (new ShutdownWorkerDirective('123', 2))->toMessage()->getPayload()['retry_count']);
    }

    public function testThrowsExceptionIfMessageCodeDoesNotMatchShutdownWorkerCode()
    {
        $this->expectException(NotShutdownWorkerDirectiveMessageException::class);
        $message = 'Unable to rehydrate from "foo" queue message, expected "shutdown_worker"';
        $this->expectExceptionMessage($message);

        ShutdownWorkerDirective::fromMessage(Message::withCurrentTime('foo', [], []));
    }

    public function testCanBeRehydratedFromMessage()
    {
        $testPid = '2233';
        $testRetryCount = 42;
        $message = (new ShutdownWorkerDirective($testPid, $testRetryCount))->toMessage();

        $rehydratedCommand = ShutdownWorkerDirective::fromMessage($message);

        $this->assertInstanceOf(ShutdownWorkerDirective::class, $rehydratedCommand);
        $this->assertSame($testPid, $rehydratedCommand->getPid());
        $this->assertSame($testRetryCount, $rehydratedCommand->getRetryCount());
    }

    /**
     * @dataProvider invalidConsumerPidProvider
     */
    public function testThrowsExceptionIfPidIsInvalid(string $invalidConsumerPid)
    {
        $this->expectException(InvalidMessageConsumerPidException::class);
        $message = sprintf('The consumer PID has to be digits or "*" for any, got "%s"', $invalidConsumerPid);
        $this->expectExceptionMessage($message);
        new ShutdownWorkerDirective($invalidConsumerPid);
    }

    public function invalidConsumerPidProvider()
    {
        return [
            [''],
            ['abc'],
            ['_'],
            ['%'],
            ['.'],
            ['1^2'],
            [' 1'],
            ['1 '],
            ['0'],
        ];
    }
}
