<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\RootTemplate\Exception\InvalidTemplateIdException;
use LizardsAndPumpkins\Import\RootTemplate\Exception\NotUpdateTemplateCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommand
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 */
class UpdateTemplateCommandTest extends TestCase
{
    /**
     * @var DataVersion
     */
    private $testDataVersion;

    private function createCommand($templateId, $templateContent): UpdateTemplateCommand
    {
        return new UpdateTemplateCommand($templateId, $templateContent, $this->testDataVersion);
    }

    protected function setUp()
    {
        $this->testDataVersion = DataVersion::fromVersionString('xyz');
    }

    public function testIsCommand()
    {
        $this->assertInstanceOf(Command::class, $this->createCommand('test_id', '...'));
    }

    /**
     * @dataProvider invalidTemplateIdProvider
     */
    public function testThrowsExceptionIfTheTemplateIdIsInvalid(string $invalidTemplateId)
    {
        $this->expectException(InvalidTemplateIdException::class);
        $this->expectExceptionMessage('Invalid template ID: ');
        new UpdateTemplateCommand($invalidTemplateId, 'example content', $this->testDataVersion);
    }

    public function invalidTemplateIdProvider(): array
    {
        return [
            'empty'               => [''],
            'leading space'       => [' xxx'],
            'trailig space'       => ['xxx '],
            'space in the middle' => ['x x'],
            'only space'          => [' '],
            'single quote'        => ["xx'xx'"],
            'double quote'        => ['yy"yy'],
            'newline'             => ["xx\n"],
            'carriage return'     => ["xx\r"],
        ];
    }

    public function testReturnsTheGivenTemplateId()
    {
        $this->assertSame('bar', $this->createCommand('bar', 'example content')->getTemplateId());
    }

    public function testReturnsTheTemplateContent()
    {
        $this->assertSame('example content', $this->createCommand('foo', 'example content')->getTemplateContent());
    }

    public function testReturnsTheDataVersion()
    {
        $this->assertSame($this->testDataVersion, $this->createCommand('example_id', 'example')->getDataVersion());
    }

    public function testCanBeConvertedIntoAMessageInstance()
    {
        $updateTemplateCommand = $this->createCommand('foo', 'bar');
        $message = $updateTemplateCommand->toMessage();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(UpdateTemplateCommand::CODE, $message->getName());
        $this->assertSame($updateTemplateCommand->getTemplateId(), $message->getPayload()['template_id']);
        $this->assertSame($updateTemplateCommand->getTemplateContent(), $message->getPayload()['template_content']);
        $this->assertEquals((string) $this->testDataVersion, $message->getMetadata()['data_version']);
    }

    public function testThrowsExceptionIfTheMessageNameDoesNotMatchTheCommandCode()
    {
        $this->expectException(NotUpdateTemplateCommandMessageException::class);
        $this->expectExceptionMessage('Invalid message code "foo", expected ' . UpdateTemplateCommand::CODE);

        UpdateTemplateCommand::fromMessage(Message::withCurrentTime('foo', [], []));
    }

    public function testCanBeRehydratedFromMessage()
    {
        $sourceCommand = $this->createCommand('foo', 'bar');
        $rehydratedCommand = UpdateTemplateCommand::fromMessage($sourceCommand->toMessage());

        $this->assertInstanceOf(UpdateTemplateCommand::class, $rehydratedCommand);
        $this->assertSame($sourceCommand->getTemplateId(), $rehydratedCommand->getTemplateId());
        $this->assertSame($sourceCommand->getTemplateContent(), $rehydratedCommand->getTemplateContent());
        $this->assertEquals((string) $sourceCommand->getDataVersion(), $rehydratedCommand->getDataVersion());
    }
}
