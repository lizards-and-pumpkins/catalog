<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Command\Exception\InvalidCommandConsumerPidException;
use LizardsAndPumpkins\Messaging\Command\Exception\NoShutdownWorkerCommandMessageException;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Messaging\Command\ShutdownWorkerCommand
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class ShutdownWorkerCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testImplementsCommandInterface()
    {
        $this->assertInstanceOf(Command::class, new ShutdownWorkerCommand('*'));
    }

    public function testReturnsMessageWithShutdownWorkerNameAndPayload()
    {
        $message = (new ShutdownWorkerCommand('123'))->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(ShutdownWorkerCommand::CODE, $message->getName());
        $this->assertSame('123', $message->getPayload()['pid']);
    }

    public function testReturnsMessageWithSpecifiedRetryCount()
    {
        $this->assertSame(0, (new ShutdownWorkerCommand('123'))->toMessage()->getPayload()['retry_count']);
        $this->assertSame(1, (new ShutdownWorkerCommand('123', 1))->toMessage()->getPayload()['retry_count']);
        $this->assertSame(2, (new ShutdownWorkerCommand('123', 2))->toMessage()->getPayload()['retry_count']);
    }

    public function testThrowsExceptionIfMessageCodeDoesNotMatchShutdownWorkerCode()
    {
        $this->expectException(NoShutdownWorkerCommandMessageException::class);
        $message = 'Unable to rehydrate from "foo" queue message, expected "shutdown_worker"';
        $this->expectExceptionMessage($message);

        ShutdownWorkerCommand::fromMessage(Message::withCurrentTime('foo', [], []));
    }

    public function testCanBeRehydratedFromMessage()
    {
        $testPid = '2233';
        $testRetryCount = 42;
        $message = (new ShutdownWorkerCommand($testPid, $testRetryCount))->toMessage();

        $rehydratedCommand = ShutdownWorkerCommand::fromMessage($message);

        $this->assertInstanceOf(ShutdownWorkerCommand::class, $rehydratedCommand);
        $this->assertSame($testPid, $rehydratedCommand->getPid());
        $this->assertSame($testRetryCount, $rehydratedCommand->getRetryCount());
    }

    /**
     * @dataProvider invalidConsumerPidProvider
     */
    public function testThrowsExceptionIfPidIsInvalid(string $invalidConsumerPid)
    {
        $this->expectException(InvalidCommandConsumerPidException::class);
        $message = sprintf('The command consumer PID has to be digits or "*" for any, got "%s"', $invalidConsumerPid);
        $this->expectExceptionMessage($message);
        new ShutdownWorkerCommand($invalidConsumerPid);
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
        ];
    }
}
