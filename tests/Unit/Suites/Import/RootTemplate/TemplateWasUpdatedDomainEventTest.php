<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\RootTemplate\Exception\NoTemplateWasUpdatedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class TemplateWasUpdatedDomainEventTest extends TestCase
{
    /**
     * @var string
     */
    private $dummyTemplateId = 'foo';

    /**
     * @var string
     */
    private $dummyTemplateContent = 'stub-projection-source-data';

    /**
     * @var TemplateWasUpdatedDomainEvent
     */
    private $domainEvent;

    /**
     * @var DataVersion
     */
    private $dummyDataVersion;

    final protected function setUp(): void
    {
        $this->dummyDataVersion = DataVersion::fromVersionString('foo');
        $this->domainEvent = new TemplateWasUpdatedDomainEvent(
            $this->dummyTemplateId,
            $this->dummyTemplateContent,
            $this->dummyDataVersion
        );
    }

    public function testDomainEventInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testTemplateContentIsReturned(): void
    {
        $this->assertSame($this->dummyTemplateContent, $this->domainEvent->getTemplateContent());
    }

    public function testTemplateIdIsReturned(): void
    {
        $this->assertSame($this->dummyTemplateId, $this->domainEvent->getTemplateId());
    }

    public function testReturnsDataVersion(): void
    {
        $this->assertSame($this->dummyDataVersion, $this->domainEvent->getDataVersion());
    }

    public function testReturnsTemplateWasUpdatedEventMessage(): void
    {
        $message = $this->domainEvent->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(TemplateWasUpdatedDomainEvent::CODE, $message->getName());
    }

    public function testReturnsMessageWithTemplatePayload(): void
    {
        $payload = $this->domainEvent->toMessage()->getPayload();
        $this->assertSame($this->dummyTemplateId, $payload['id']);
        $this->assertSame($this->dummyTemplateContent, $payload['template']);
    }

    public function testReturnsMessageWithMetadata(): void
    {
        $metadata = $this->domainEvent->toMessage()->getMetadata();
        $this->assertSame((string) $this->dummyDataVersion, $metadata[DataVersion::VERSION_KEY]);
    }

    public function testCanBeRehydratedFromMessage(): void
    {
        $rehydratedEvent = TemplateWasUpdatedDomainEvent::fromMessage($this->domainEvent->toMessage());
        $this->assertInstanceOf(TemplateWasUpdatedDomainEvent::class, $rehydratedEvent);
        $this->assertSame($this->dummyTemplateId, $rehydratedEvent->getTemplateId());
        $this->assertSame($this->dummyTemplateContent, $rehydratedEvent->getTemplateContent());
        $this->assertEquals((string) $this->dummyDataVersion, $rehydratedEvent->getDataVersion());
    }

    public function testThrowsExceptionIfMessageNameDoesNotMatch(): void
    {
        $this->expectException(NoTemplateWasUpdatedDomainEventMessageException::class);
        $this->expectExceptionMessage('Expected "template_was_updated" domain event, got "foo"');
        TemplateWasUpdatedDomainEvent::fromMessage(Message::withCurrentTime('foo', [], []));
    }
}
