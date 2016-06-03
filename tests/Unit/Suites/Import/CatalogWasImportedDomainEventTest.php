<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Exception\NoCatalogWasImportedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\CatalogWasImportedDomainEvent
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 */
class CatalogWasImportedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CatalogWasImportedDomainEvent
     */
    private $event;

    /**
     * @var DataVersion|\PHPUnit_Framework_MockObject_MockObject
     */
    private $testDataVersion;

    protected function setUp()
    {
        $this->testDataVersion = DataVersion::fromVersionString('1234');
        
        $this->event = new CatalogWasImportedDomainEvent($this->testDataVersion);
    }

    public function testIsADomainEvent()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->event);
    }

    public function testReturnsTheInjectedVersion()
    {
        $this->assertSame($this->testDataVersion, $this->event->getDataVersion());
    }

    public function testReturnsMessageWithEventCodeAsName()
    {
        $message = $this->event->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(CatalogWasImportedDomainEvent::CODE, $message->getName());
    }

    public function testReturnsMessageWithDataVersionInMetaData()
    {
        $message = $this->event->toMessage();
        $this->assertArrayHasKey('data_version', $message->getMetadata());
        $this->assertSame((string) $this->testDataVersion, $message->getMetadata()['data_version']);
    }

    public function testCanBeRehydratedFromMessage()
    {
        $message = $this->event->toMessage();
        $rehydratedEvent = CatalogWasImportedDomainEvent::fromMessage($message);
        $this->assertInstanceOf(CatalogWasImportedDomainEvent::class, $rehydratedEvent);
        $this->assertSame((string) $rehydratedEvent->getDataVersion(), (string) $this->testDataVersion);
    }

    public function testThrowsExceptionIfMessageNameDoesNotMatchEventCode()
    {
        $this->expectException(NoCatalogWasImportedDomainEventMessageException::class);
        $this->expectExceptionMessage('Expected "catalog_was_imported" domain event, got "buz"');
        
        CatalogWasImportedDomainEvent::fromMessage(Message::withCurrentTime('buz', '', []));
    }
}
