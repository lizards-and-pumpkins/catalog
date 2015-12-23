<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\UnableToFindDomainEventHandlerException;
use LizardsAndPumpkins\Image\ImageWasAddedDomainEvent;
use LizardsAndPumpkins\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent;
use LizardsAndPumpkins\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEvent;
use LizardsAndPumpkins\Product\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\Projection\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Projection\TemplateWasUpdatedDomainEventHandler;

/**
 * @covers \LizardsAndPumpkins\DomainEventHandlerLocator
 * @uses   \LizardsAndPumpkins\Product\ProductListingWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Image\ImageWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Product\ProductWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Projection\TemplateWasUpdatedDomainEvent
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

    public function testImageWasAddedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ImageWasAddedDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createImageWasAddedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var ImageWasAddedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMockBuilder(ImageWasAddedDomainEvent::class)
            ->setMockClassName('ImageWasAddedDomainEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ImageWasAddedDomainEventHandler::class, $result);
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
}
