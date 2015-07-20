<?php

namespace Brera;

use Brera\Image\ImageImportDomainEvent;
use Brera\Image\ImageImportDomainEventHandler;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductWasUpdatedDomainEvent;
use Brera\Product\ProductWasUpdatedDomainEventHandler;
use Brera\Product\ProductListingWasUpdatedDomainEvent;
use Brera\Product\ProductListingWasUpdatedDomainEventHandler;
use Brera\Product\ProductStockQuantityUpdatedDomainEvent;
use Brera\Product\ProductStockQuantityUpdatedDomainEventHandler;

/**
 * @covers \Brera\DomainEventHandlerLocator
 * @uses   \Brera\Image\ImageImportDomainEvent
 * @uses   \Brera\Product\CatalogImportDomainEvent
 * @uses   \Brera\Product\ProductListingWasUpdatedDomainEvent
 * @uses   \Brera\Product\ProductStockQuantityUpdatedDomainEvent
 * @uses   \Brera\Product\ProductWasUpdatedDomainEvent
 * @uses   \Brera\RootTemplateChangedDomainEvent
 */
class DomainEventHandlerLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomainEventHandlerLocator
     */
    private $locator;

    /**
     * @var CommonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->getMock(CommonFactory::class);
        $this->locator = new DomainEventHandlerLocator($this->factory);
    }

    public function testExceptionIsThrownIfNoHandlerIsLocated()
    {
        /** @var DomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->setExpectedException(UnableToFindDomainEventHandlerException::class);
        $this->locator->getHandlerFor($stubDomainEvent);
    }

    public function testProductWasUpdatedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ProductWasUpdatedDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createProductWasUpdatedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var ProductWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMockBuilder(ProductWasUpdatedDomainEvent::class)
            ->setMockClassName('ProductWasUpdatedDomainEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ProductWasUpdatedDomainEventHandler::class, $result);
    }

    public function testCatalogImportDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(CatalogImportDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createCatalogImportDomainEventHandler')->willReturn($stubEventHandler);

        /** @var CatalogImportDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMockBuilder(CatalogImportDomainEvent::class)
            ->setMockClassName('CatalogImportDomainEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(CatalogImportDomainEventHandler::class, $result);
    }

    public function testRootTemplateChangedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(RootTemplateChangedDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createRootTemplateChangedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var RootTemplateChangedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMockBuilder(RootTemplateChangedDomainEvent::class)
            ->setMockClassName('RootTemplateChangedDomainEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(RootTemplateChangedDomainEventHandler::class, $result);
    }

    public function testImageImportDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ImageImportDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createImageImportDomainEventHandler')->willReturn($stubEventHandler);

        /** @var ImageImportDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMockBuilder(ImageImportDomainEvent::class)
            ->setMockClassName('ImageImportDomainEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ImageImportDomainEventHandler::class, $result);
    }

    public function testProductListingWasUpdatedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ProductListingWasUpdatedDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createProductListingWasUpdatedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var ProductListingWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMockBuilder(ProductListingWasUpdatedDomainEvent::class)
            ->setMockClassName('ProductListingWasUpdatedDomainEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ProductListingWasUpdatedDomainEventHandler::class, $result);
    }

    public function testProductStockQuantityChangedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ProductStockQuantityUpdatedDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createProductStockQuantityUpdatedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var ProductStockQuantityUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMockBuilder(ProductStockQuantityUpdatedDomainEvent::class)
            ->setMockClassName('ProductStockQuantityUpdatedDomainEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ProductStockQuantityUpdatedDomainEventHandler::class, $result);
    }
}
