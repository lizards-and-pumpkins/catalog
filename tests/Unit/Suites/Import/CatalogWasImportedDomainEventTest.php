<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Exception\NoCatalogWasImportedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\CatalogWasImportedDomainEvent
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class CatalogWasImportedDomainEventTest extends TestCase
{
    /**
     * @var CatalogWasImportedDomainEvent
     */
    private $event;

    /**
     * @var DataVersion|MockObject
     */
    private $testDataVersion;

    final protected function setUp(): void
    {
        $this->testDataVersion = DataVersion::fromVersionString('1234');
        
        $this->event = new CatalogWasImportedDomainEvent($this->testDataVersion);
    }

    public function testIsADomainEvent(): void
    {
        $this->assertInstanceOf(DomainEvent::class, $this->event);
    }

    public function testReturnsTheInjectedVersion(): void
    {
        $this->assertSame($this->testDataVersion, $this->event->getDataVersion());
    }

    public function testReturnsMessageWithEventCodeAsName(): void
    {
        $message = $this->event->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(CatalogWasImportedDomainEvent::CODE, $message->getName());
    }

    public function testReturnsMessageWithDataVersionInMetaData(): void
    {
        $message = $this->event->toMessage();
        $this->assertArrayHasKey('data_version', $message->getMetadata());
        $this->assertSame((string) $this->testDataVersion, $message->getMetadata()['data_version']);
    }

    public function testCanBeRehydratedFromMessage(): void
    {
        $message = $this->event->toMessage();
        $rehydratedEvent = CatalogWasImportedDomainEvent::fromMessage($message);
        $this->assertInstanceOf(CatalogWasImportedDomainEvent::class, $rehydratedEvent);
        $this->assertSame((string) $rehydratedEvent->getDataVersion(), (string) $this->testDataVersion);
    }

    public function testThrowsExceptionIfMessageNameDoesNotMatchEventCode(): void
    {
        $this->expectException(NoCatalogWasImportedDomainEventMessageException::class);
        $this->expectExceptionMessage('Expected "catalog_was_imported" domain event, got "buz"');
        
        CatalogWasImportedDomainEvent::fromMessage(Message::withCurrentTime('buz', [], []));
    }
}
