<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
use LizardsAndPumpkins\Import\ContentBlock\Exception\NoContentBlockWasUpdatedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 */
class ContentBlockWasUpdatedDomainEventTest extends TestCase
{
    /**
     * @var ContentBlockId|MockObject
     */
    private $stubContentBlockId;

    /**
     * @var ContentBlockSource|MockObject
     */
    private $stubContentBlockSource;

    /**
     * @var ContentBlockWasUpdatedDomainEvent
     */
    private $domainEvent;

    final protected function setUp(): void
    {
        $this->stubContentBlockId = $this->createMock(ContentBlockId::class);
        $this->stubContentBlockSource = $this->createMock(ContentBlockSource::class);
        $this->stubContentBlockSource->method('getContentBlockId')->willReturn($this->stubContentBlockId);
        $this->stubContentBlockSource->method('serialize')->willReturn('');
        $this->domainEvent = new ContentBlockWasUpdatedDomainEvent($this->stubContentBlockSource);
    }

    public function testDomainEventInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testContentBlockSourceIsReturned(): void
    {
        $result = $this->domainEvent->getContentBlockSource();
        $this->assertSame($this->stubContentBlockSource, $result);
    }

    public function testReturnsContentBlockWasUpdatedMessage(): void
    {
        $message = $this->domainEvent->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(ContentBlockWasUpdatedDomainEvent::CODE, $message->getName());
    }

    public function testReturnsMessageWithContentBlockPayload(): void
    {
        $payload = $this->domainEvent->toMessage()->getPayload();
        $this->assertArrayHasKey('id', $payload);
        $this->assertArrayHasKey('source', $payload);
    }

    public function testCanBeRehydratedFromMessage(): void
    {
        $sourceContentBlock = new ContentBlockSource(
            ContentBlockId::fromString('test'),
            '',
            SelfContainedContextBuilder::rehydrateContext([]),
            []
        );
        $message = (new ContentBlockWasUpdatedDomainEvent($sourceContentBlock))->toMessage();
        $rehydratedEvent = ContentBlockWasUpdatedDomainEvent::fromMessage($message);
        $this->assertInstanceOf(ContentBlockWasUpdatedDomainEvent::class, $rehydratedEvent);
        $this->assertSame(
            (string) $sourceContentBlock->getContentBlockId(),
            (string) $rehydratedEvent->getContentBlockSource()->getContentBlockId()
        );
    }

    public function testThrowsExceptionIfMessageNameDoesNotMatch(): void
    {
        $this->expectException(NoContentBlockWasUpdatedDomainEventMessageException::class);
        $this->expectExceptionMessage('Expected "content_block_was_updated" domain event, got "foobar"');
        ContentBlockWasUpdatedDomainEvent::fromMessage(Message::withCurrentTime('foobar', [], []));
    }
}
