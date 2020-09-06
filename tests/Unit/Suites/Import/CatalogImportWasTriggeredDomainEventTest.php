<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Exception\NoCatalogWasImportedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEvent
 * @uses \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class CatalogImportWasTriggeredDomainEventTest extends TestCase
{
    /**
     * @var CatalogImportWasTriggeredDomainEvent
     */
    private $domainEvent;

    private $testDataVersionString = 'xyz';
    
    private $testImportFilePath = __FILE__;

    final protected function setUp(): void
    {
        $dataVersion = DataVersion::fromVersionString($this->testDataVersionString);
        $this->domainEvent = new CatalogImportWasTriggeredDomainEvent($dataVersion, $this->testImportFilePath);
    }
    
    public function testImplementsDomainEvent(): void
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testReturnsTheDataVersion(): void
    {
        $this->assertSame($this->testDataVersionString, (string) $this->domainEvent->getDataVersion());
    }

    public function testReturnsTheCatalogImportFilePath(): void
    {
        $this->assertSame($this->testImportFilePath, $this->domainEvent->getCatalogImportFilePath());
    }

    public function testSerializesItselfAsAMessage(): void
    {
        $message = $this->domainEvent->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(CatalogImportWasTriggeredDomainEvent::CODE, $message->getName());
        $this->assertSame($this->testDataVersionString, $message->getMetadata()['data_version']);
        $this->assertSame($this->testImportFilePath, $message->getPayload()['import_file_path']);
    }

    public function testThrowsExceptionIfMessageCodeDoesNotMatch(): void
    {
        $this->expectException(NoCatalogWasImportedDomainEventMessageException::class);
        $this->expectExceptionMessage('Invalid domain event "foo", expected "' . CatalogImportWasTriggeredDomainEvent::CODE);
        CatalogImportWasTriggeredDomainEvent::fromMessage(Message::withCurrentTime('foo', [], []));
    }

    public function testCanBeRehydratedFromMessage(): void
    {
        $rehydratedDomainEvent = CatalogImportWasTriggeredDomainEvent::fromMessage($this->domainEvent->toMessage());
        $this->assertInstanceOf(CatalogImportWasTriggeredDomainEvent::class, $rehydratedDomainEvent);
    }
}
