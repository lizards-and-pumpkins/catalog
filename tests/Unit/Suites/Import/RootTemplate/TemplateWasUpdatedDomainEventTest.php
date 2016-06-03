<?php

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Import\RootTemplate\Exception\NoTemplateWasUpdatedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class TemplateWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->domainEvent = new TemplateWasUpdatedDomainEvent($this->dummyTemplateId, $this->dummyTemplateContent);
    }

    public function testDomainEventInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testTemplatecontentIsReturned()
    {
        $result = $this->domainEvent->getTemplateContent();
        $this->assertSame($this->dummyTemplateContent, $result);
    }

    public function testTemplateIdIsReturned()
    {
        $result = $this->domainEvent->getTemplateId();
        $this->assertSame($this->dummyTemplateId, $result);
    }

    public function testReturnsTemplateWasUpdatedEventMessage()
    {
        $message = $this->domainEvent->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(TemplateWasUpdatedDomainEvent::CODE, $message->getName());
    }

    public function testReturnsMessageWithTemplatePayload()
    {
        $payload = $this->domainEvent->toMessage()->getPayload();
        $this->assertArrayHasKey('id', $payload);
        $this->assertSame($this->dummyTemplateId, $payload['id']);
        $this->assertArrayHasKey('template', $payload);
        $this->assertSame($this->dummyTemplateContent, $payload['template']);
    }

    public function testCanBeRehydratedFromMessage()
    {
        $rehydratedEvent = TemplateWasUpdatedDomainEvent::fromMessage($this->domainEvent->toMessage());
        $this->assertInstanceOf(TemplateWasUpdatedDomainEvent::class, $rehydratedEvent);
        $this->assertSame($this->dummyTemplateId, $rehydratedEvent->getTemplateId());
        $this->assertSame($this->dummyTemplateContent, $rehydratedEvent->getTemplateContent());
    }

    public function testThrowsExceptionIfMessageNameDoesNotMatch()
    {
        $this->expectException(NoTemplateWasUpdatedDomainEventMessageException::class);
        $this->expectExceptionMessage('Expected "template_was_updated" domain event, got "foo"');
        TemplateWasUpdatedDomainEvent::fromMessage(Message::withCurrentTime('foo', [], []));
    }
}
