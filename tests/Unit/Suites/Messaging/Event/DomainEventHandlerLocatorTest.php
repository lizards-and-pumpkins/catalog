<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Event\Exception\UnableToFindDomainEventHandlerException;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\DomainEventHandlerLocator
 * @uses   \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent
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
            ->setMethods(array_merge(get_class_methods(DomainEventHandlerFactory::class), ['register']))
            ->getMock();
        $this->locator = new DomainEventHandlerLocator($this->factory);
    }

    public function testExceptionIsThrownIfNoHandlerIsLocated()
    {
        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(Message::class, [], [], '', false);
        $stubDomainEvent->method('getName')->willReturn('non_existing_domain_event');
        $this->expectException(UnableToFindDomainEventHandlerException::class);
        $this->locator->getHandlerFor($stubDomainEvent);
    }

    public function testProductWasUpdatedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ProductWasUpdatedDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createProductWasUpdatedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(Message::class, [], [], '', false);
        $stubDomainEvent->method('getName')->willReturn('product_was_updated_domain_event');

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ProductWasUpdatedDomainEventHandler::class, $result);
    }

    public function testTemplateWasUpdatedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(TemplateWasUpdatedDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createTemplateWasUpdatedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(Message::class, [], [], '', false);
        $stubDomainEvent->method('getName')->willReturn('template_was_updated_domain_event');

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(TemplateWasUpdatedDomainEventHandler::class, $result);
    }

    public function testImageWasAddedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ImageWasAddedDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createImageWasAddedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(Message::class, [], [], '', false);
        $stubDomainEvent->method('getName')->willReturn('image_was_added_domain_event');
        
        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ImageWasAddedDomainEventHandler::class, $result);
    }

    public function testProductListingWasAddedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ProductListingWasAddedDomainEventHandler::class, [], [], '', false);
        $this->factory->method('createProductListingWasAddedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(Message::class, [], [], '', false);
        $stubDomainEvent->method('getName')->willReturn('product_listing_was_added_domain_event');
        
        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ProductListingWasAddedDomainEventHandler::class, $result);
    }
}
