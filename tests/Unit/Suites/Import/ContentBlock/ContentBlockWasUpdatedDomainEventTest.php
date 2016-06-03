<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\ContentBlock\Exception\NoContentBlockWasUpdatedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 */
class ContentBlockWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentBlockId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContentBlockId;

    /**
     * @var ContentBlockSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContentBlockSource;

    /**
     * @var ContentBlockWasUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $this->stubContentBlockId = $this->getMock(ContentBlockId::class, [], [], '', false);
        $this->stubContentBlockSource = $this->getMock(ContentBlockSource::class, [], [], '', false);
        $this->stubContentBlockSource->method('getContentBlockId')->willReturn($this->stubContentBlockId);
        $this->domainEvent = new ContentBlockWasUpdatedDomainEvent($this->stubContentBlockSource);
    }

    public function testDomainEventInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testContentBlockSourceIsReturned()
    {
        $result = $this->domainEvent->getContentBlockSource();
        $this->assertSame($this->stubContentBlockSource, $result);
    }

    public function testReturnsContentBlockWasUpdatedMessage()
    {
        $message = $this->domainEvent->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(ContentBlockWasUpdatedDomainEvent::CODE, $message->getName());
    }

    public function testReturnsMessagWithContentBlockPayload()
    {
        $payload = json_decode($this->domainEvent->toMessage()->getPayload(), true);
        $this->assertArrayHasKey('id', $payload);
        $this->assertArrayHasKey('source', $payload);
    }

    public function testCanBeRehydratedFromMessage()
    {
        $sourceContentBlock = new ContentBlockSource(
            ContentBlockId::fromString('test'),
            '',
            [],
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

    public function testThrowsExceptionIfMessageNameDoesNotMatch()
    {
        $this->expectException(NoContentBlockWasUpdatedDomainEventMessageException::class);
        $this->expectExceptionMessage('Expected "content_block_was_updated" domain event, got "foobar"');
        ContentBlockWasUpdatedDomainEvent::fromMessage(Message::withCurrentTime('foobar', '', []));
    }
}
