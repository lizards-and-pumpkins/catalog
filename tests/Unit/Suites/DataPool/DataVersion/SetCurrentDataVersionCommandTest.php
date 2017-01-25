<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataVersion\Exception\NotSetCurrentDataVersionCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommand
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class SetCurrentDataVersionCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testImplementsCommandInterface()
    {
        $testDataVersion = DataVersion::fromVersionString('foo');
        $this->assertInstanceOf(Command::class, new SetCurrentDataVersionCommand($testDataVersion));
    }

    public function testReturnsDataVersionToSet()
    {
        $testDataVersion = DataVersion::fromVersionString('bar');
        $this->assertSame($testDataVersion, (new SetCurrentDataVersionCommand($testDataVersion))->getDataVersion());
    }

    public function testReturnsMessageWithDataVersion()
    {
        $testDataVersion = DataVersion::fromVersionString('baz');
        $message = (new SetCurrentDataVersionCommand($testDataVersion))->toMessage();
        $this->assertSame(SetCurrentDataVersionCommand::CODE, $message->getName());
        $this->assertSame('baz', $message->getMetadata()['data_version']);
    }

    public function testThrowsExceptionIfTheMessageNameDoesNotMatchSetCurrentDataVersion()
    {
        $code = 'foo';
        $this->expectException(NotSetCurrentDataVersionCommandMessageException::class);
        $expectedMessage = sprintf('Expected message name %s, got "%s"', SetCurrentDataVersionCommand::CODE, $code);
        $this->expectExceptionMessage($expectedMessage);
        SetCurrentDataVersionCommand::fromMessage(Message::withCurrentTime($code, [], []));
    }

    public function testCanBeRehydratedFromMessage()
    {
        $testDataVersion = DataVersion::fromVersionString('baz');
        $message = (new SetCurrentDataVersionCommand($testDataVersion))->toMessage();
        $command = SetCurrentDataVersionCommand::fromMessage($message);
        $this->assertInstanceOf(SetCurrentDataVersionCommand::class, $command);
        $this->assertEquals((string) $testDataVersion, $command->getDataVersion());
    }
}
