<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirectiveHandler;
use LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEventHandler;
use LizardsAndPumpkins\Import\CatalogImportWasTriggeredDomainEvent;
use LizardsAndPumpkins\Messaging\Event\Exception\UnableToFindDomainEventHandlerException;
use LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

/**
 * @covers \LizardsAndPumpkins\Messaging\Event\DomainEventHandlerLocator
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
        $stubDomainEvent = $this->createMock(Message::class);
        $stubDomainEvent->method('getName')->willReturn('non_existing_domain_event');
        $this->expectException(UnableToFindDomainEventHandlerException::class);
        $this->locator->getHandlerFor($stubDomainEvent);
    }

    public function testProductWasUpdatedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->createMock(ProductWasUpdatedDomainEventHandler::class);
        $this->factory->method('createProductWasUpdatedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->createMock(Message::class);
        $stubDomainEvent->method('getName')->willReturn('product_was_updated');

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ProductWasUpdatedDomainEventHandler::class, $result);
    }

    public function testTemplateWasUpdatedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->createMock(TemplateWasUpdatedDomainEventHandler::class);
        $this->factory->method('createTemplateWasUpdatedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->createMock(Message::class);
        $stubDomainEvent->method('getName')->willReturn('template_was_updated');

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(TemplateWasUpdatedDomainEventHandler::class, $result);
    }

    public function testImageWasAddedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->createMock(ImageWasAddedDomainEventHandler::class);
        $this->factory->method('createImageWasAddedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->createMock(Message::class);
        $stubDomainEvent->method('getName')->willReturn('image_was_added');
        
        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ImageWasAddedDomainEventHandler::class, $result);
    }

    public function testProductListingWasAddedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->createMock(ProductListingWasAddedDomainEventHandler::class);
        $this->factory->method('createProductListingWasAddedDomainEventHandler')->willReturn($stubEventHandler);

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->createMock(Message::class);
        $stubDomainEvent->method('getName')->willReturn('product_listing_was_added');
        
        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ProductListingWasAddedDomainEventHandler::class, $result);
    }

    public function testReturnsShutdownWorkerDomainEventHandler()
    {
        $stubEventHandler = $this->createMock(ShutdownWorkerDirectiveHandler::class);
        $this->factory->method('createShutdownWorkerDomainEventHandler')->willReturn($stubEventHandler);

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->createMock(Message::class);
        $stubDomainEvent->method('getName')->willReturn('shutdown_worker');

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(ShutdownWorkerDirectiveHandler::class, $result);
    }

    public function testReturnsCatalogImportWasTriggeredDomainEventHandler()
    {
        $stubEventHandler = $this->createMock(CatalogImportWasTriggeredDomainEventHandler::class);
        $this->factory->method('createCatalogImportWasTriggeredDomainEventHandler')->willReturn($stubEventHandler);

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->createMock(Message::class);
        $stubDomainEvent->method('getName')->willReturn(CatalogImportWasTriggeredDomainEvent::CODE);

        $result = $this->locator->getHandlerFor($stubDomainEvent);

        $this->assertInstanceOf(CatalogImportWasTriggeredDomainEventHandler::class, $result);
    }
}
