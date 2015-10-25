<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\DomainEventHandler;
use LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetProjector;

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

    /**
     * @var ProductListingPageSnippetProjector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockListingGeneration;

    protected function setUp()
    {
        $this->stubVersion = $this->getMock(DataVersion::class, [], [], '', false);
        $this->stubEvent = $this->getMock(CatalogWasImportedDomainEvent::class, [], [], '', false);
        $this->stubEvent->method('getDataVersion')->willReturn($this->stubVersion);
        
        $this->mockListingGeneration = $this->getMockBuilder(ProductListingPageSnippetProjector::class)
            ->disableOriginalConstructor()
            ->setMethods(['project'])
            ->getMock();
        
        $this->eventHandler = new CatalogWasImportedDomainEventHandler(
            $this->stubEvent,
            $this->mockListingGeneration
        );
    }
    
    public function testItIsAnDomainEventHandler()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->eventHandler);
    }

    public function testItTriggersTheProductListingProjection()
    {
        $this->mockListingGeneration->expects($this->once())->method('project')->with($this->stubVersion);
        $this->eventHandler->process();
    }
}
