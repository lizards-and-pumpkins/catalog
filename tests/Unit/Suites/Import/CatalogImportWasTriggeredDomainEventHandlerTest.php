<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEventHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEvent
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class CatalogImportWasTriggeredDomainEventHandlerTest extends TestCase
{
    private $testFile = '/test.xml';

    /**
     * @var DataVersion
     */
    private $testDataVersion;

    /**
     * @var CatalogImport|MockObject
     */
    private $mockCatalogImport;

    /**
     * @var CatalogImportWasTriggeredDomainEventHandler
     */
    private $domainEventHandler;

    final protected function setUp(): void
    {
        $this->mockCatalogImport = $this->createMock(CatalogImport::class);
        $this->testDataVersion = DataVersion::fromVersionString('foo');
        $this->domainEventHandler = new CatalogImportWasTriggeredDomainEventHandler($this->mockCatalogImport);
    }
    
    public function testImplementsDomainEventHandler(): void
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testDelegatesProcessingTheImportFileToCatalogImport(): void
    {

        $testMessage = (new CatalogImportWasTriggeredDomainEvent($this->testDataVersion, $this->testFile))->toMessage();
            
        $this->mockCatalogImport->expects($this->once())->method('importFile')
            ->with($this->testFile, $this->testDataVersion);
        $this->domainEventHandler->process($testMessage);
    }
}
