<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\ContentBlock\Exception\NoUpdateContentBlockCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 */
class UpdateContentBlockCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentBlockSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContentBlockSource;

    /**
     * @var UpdateContentBlockCommand
     */
    private $command;

    protected function setUp()
    {
        $this->stubContentBlockSource = $this->createMock(ContentBlockSource::class);
        $this->stubContentBlockSource->method('serialize')->willReturn(json_encode('foo'));
        $this->command = new UpdateContentBlockCommand($this->stubContentBlockSource);
    }

    public function testCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testContentBlockSourceIsReturned()
    {
        $result = $this->command->getContentBlockSource();
        $this->assertSame($this->stubContentBlockSource, $result);
    }

    public function testReturnsMessageWithName()
    {
        $message = $this->command->toMessage();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(UpdateContentBlockCommand::CODE, $message->getName());
    }

    public function testReturnsMessageWithContentBlockPayload()
    {
        $message = $this->command->toMessage();

        $this->assertSame(['block' => json_encode('foo')], $message->getPayload());
        $this->assertSame([], $message->getMetadata());
    }

    public function testCanBeRehydratedFromMessage()
    {
        $testContent = 'some empty content';
        $testContentBlockSource = new ContentBlockSource(ContentBlockId::fromString('test'), $testContent, [], []);
        $message = (new UpdateContentBlockCommand($testContentBlockSource))->toMessage();

        $rehydratedCommand = UpdateContentBlockCommand::fromMessage($message);

        $this->assertInstanceOf(UpdateContentBlockCommand::class, $rehydratedCommand);
        $rehydratedContentBlockSource = $rehydratedCommand->getContentBlockSource();
        $this->assertSame($testContent, $rehydratedContentBlockSource->getContent());
    }

    public function testThrowsExceptionIfTheMessageNameDoesNotMatchCommandCode()
    {
        $this->expectException(NoUpdateContentBlockCommandMessageException::class);
        $message = 'Unable to rehydrate from "foo" queue message, expected "update_content_block"';
        $this->expectExceptionMessage($message);

        UpdateContentBlockCommand::fromMessage(Message::withCurrentTime('foo', [], []));
    }
}
