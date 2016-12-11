<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;

/**
 * @covers \LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEventHandler
 */
class CatalogImportWasTriggeredDomainEventHandlerTest extends \PHPUnit\Framework\TestCase
{
    private $testFile = '/test.xml';

    /**
     * @var CatalogImport|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCatalogImport;

    /**
     * @var CatalogImportWasTriggeredEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockImportWasTriggeredDomainEvent;

    /**
     * @var CatalogImportWasTriggeredDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        $this->mockCatalogImport = $this->createMock(CatalogImport::class);
        $this->mockImportWasTriggeredDomainEvent = $this->createMock(CatalogImportWasTriggeredEvent::class);
        $this->mockImportWasTriggeredDomainEvent->method('getCatalogImportFilePath')->willReturn($this->testFile);
        $this->domainEventHandler = new CatalogImportWasTriggeredDomainEventHandler(
            $this->mockCatalogImport,
            $this->mockImportWasTriggeredDomainEvent
        );
    }
    
    public function testImplementsDomainEventHandler()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testDelegatesProcessingTheImportFileToCatalogImport()
    {
        $this->mockCatalogImport->expects($this->once())->method('importFile')->with($this->testFile);
        $this->domainEventHandler->process();
    }
}
