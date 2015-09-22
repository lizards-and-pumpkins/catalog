<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Image\ImageWasUpdatedDomainEvent;
use LizardsAndPumpkins\Image\ImageWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEvent;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductStockQuantityWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductStockQuantityWasUpdatedDomainEventHandler;

/**
 * @covers \LizardsAndPumpkins\DomainEventHandlerLocator
 * @uses   \LizardsAndPumpkins\Image\ImageWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Product\ProductListingWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Product\ProductStockQuantityWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\TemplateWasUpdatedDomainEvent
 */
class DomainEventHandlerLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomainEventHandlerLocator
     */
    private $locator;

    /**
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge(get_class_methods(DomainEventFactory::class), ['register']))
            ->getMock();
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

    public function testTemplateWasUpdatedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(TemplateWasUpdatedDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createTemplateWasUpdatedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var TemplateWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMockBuilder(TemplateWasUpdatedDomainEvent::class)
            ->setMockClassName('TemplateWasUpdatedDomainEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(TemplateWasUpdatedDomainEventHandler::class, $result);
    }

    public function testImageWasUpdatedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ImageWasUpdatedDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createImageWasUpdatedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var ImageWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMockBuilder(ImageWasUpdatedDomainEvent::class)
            ->setMockClassName('ImageWasUpdatedDomainEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ImageWasUpdatedDomainEventHandler::class, $result);
    }

    public function testProductListingWasAddedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ProductListingWasAddedDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createProductListingWasAddedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var ProductListingWasAddedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMockBuilder(ProductListingWasAddedDomainEvent::class)
            ->setMockClassName('ProductListingWasAddedDomainEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ProductListingWasAddedDomainEventHandler::class, $result);
    }

    public function testProductStockQuantityChangedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ProductStockQuantityWasUpdatedDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createProductStockQuantityWasUpdatedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var ProductStockQuantityWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMockBuilder(ProductStockQuantityWasUpdatedDomainEvent::class)
            ->setMockClassName('ProductStockQuantityWasUpdatedDomainEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ProductStockQuantityWasUpdatedDomainEventHandler::class, $result);
    }
}
