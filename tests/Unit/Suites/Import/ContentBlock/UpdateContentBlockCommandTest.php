<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
use LizardsAndPumpkins\Import\ContentBlock\Exception\NoUpdateContentBlockCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 */
class UpdateContentBlockCommandTest extends TestCase
{
    /**
     * @var ContentBlockSource|MockObject
     */
    private $stubContentBlockSource;

    /**
     * @var UpdateContentBlockCommand
     */
    private $command;

    final protected function setUp(): void
    {
        $this->stubContentBlockSource = $this->createMock(ContentBlockSource::class);
        $this->stubContentBlockSource->method('serialize')->willReturn(json_encode('foo'));
        $this->command = new UpdateContentBlockCommand($this->stubContentBlockSource);
    }

    public function testCommandInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testContentBlockSourceIsReturned(): void
    {
        $result = $this->command->getContentBlockSource();
        $this->assertSame($this->stubContentBlockSource, $result);
    }

    public function testReturnsMessageWithName(): void
    {
        $message = $this->command->toMessage();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(UpdateContentBlockCommand::CODE, $message->getName());
    }

    public function testReturnsMessageWithContentBlockPayload(): void
    {
        $message = $this->command->toMessage();

        $this->assertSame(['block' => json_encode('foo')], $message->getPayload());
        $this->assertSame([], $message->getMetadata());
    }

    public function testCanBeRehydratedFromMessage(): void
    {
        $testContent = 'some empty content';
        $testContext = SelfContainedContextBuilder::rehydrateContext([]);
        $contentBlockId = ContentBlockId::fromString('test');
        $testContentBlockSource = new ContentBlockSource($contentBlockId, $testContent, $testContext, []);
        $message = (new UpdateContentBlockCommand($testContentBlockSource))->toMessage();

        $rehydratedCommand = UpdateContentBlockCommand::fromMessage($message);

        $this->assertInstanceOf(UpdateContentBlockCommand::class, $rehydratedCommand);
        $rehydratedContentBlockSource = $rehydratedCommand->getContentBlockSource();
        $this->assertSame($testContent, $rehydratedContentBlockSource->getContent());
    }

    public function testThrowsExceptionIfTheMessageNameDoesNotMatchCommandCode(): void
    {
        $this->expectException(NoUpdateContentBlockCommandMessageException::class);
        $message = 'Unable to rehydrate from "foo" queue message, expected "update_content_block"';
        $this->expectExceptionMessage($message);

        UpdateContentBlockCommand::fromMessage(Message::withCurrentTime('foo', [], []));
    }
}
