<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\DomainEventHandler;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\CatalogWasImportedDomainEventHandler
 */
class CatalogWasImportedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CatalogWasImportedDomainEventHandler
     */
    private $eventHandler;

    /**
     * @var CatalogWasImportedDomainEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubEvent;

    /**
     * @var DataVersion|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubVersion;

    protected function setUp()
    {
        $this->stubVersion = $this->getMock(DataVersion::class, [], [], '', false);
        $this->stubEvent = $this->getMock(CatalogWasImportedDomainEvent::class, [], [], '', false);
        $this->stubEvent->method('getDataVersion')->willReturn($this->stubVersion);
        
        $this->eventHandler = new CatalogWasImportedDomainEventHandler($this->stubEvent);
    }
    
    public function testItIsAnDomainEventHandler()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->eventHandler);
    }

    public function testItTriggersTheProductListingProjection()
    {
        $this->markTestIncomplete('This event handler is currently not doing anything.');
        $this->eventHandler->process();
    }
}
