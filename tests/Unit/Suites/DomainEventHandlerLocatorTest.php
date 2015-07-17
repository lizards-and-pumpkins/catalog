<?php

namespace Brera;

use Brera\Image\ImageImportDomainEvent;
use Brera\Image\ImageImportDomainEventHandler;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductId;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;
use Brera\Product\ProductListingSavedDomainEvent;
use Brera\Product\ProductListingSavedDomainEventHandler;
use Brera\Product\ProductStockQuantitySource;
use Brera\Product\ProductStockQuantityUpdatedDomainEvent;
use Brera\Product\ProductStockQuantityUpdatedDomainEventHandler;

/**
 * @covers \Brera\DomainEventHandlerLocator
 * @uses   \Brera\Image\ImageImportDomainEvent
 * @uses   \Brera\Product\CatalogImportDomainEvent
 * @uses   \Brera\Product\ProductImportDomainEvent
 * @uses   \Brera\Product\ProductListingSavedDomainEvent
 * @uses   \Brera\Product\ProductStockQuantityUpdatedDomainEvent
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
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->setExpectedException(UnableToFindDomainEventHandlerException::class);
        $this->locator->getHandlerFor($stubDomainEvent);
    }

    public function testProductImportDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ProductImportDomainEventHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createProductImportDomainEventHandler')
            ->willReturn($stubEventHandler);

        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $productImportDomainEvent = new ProductImportDomainEvent('<xml/>');

        $result = $this->locator->getHandlerFor($productImportDomainEvent);

        $this->assertInstanceOf(ProductImportDomainEventHandler::class, $result);
    }

    public function testCatalogImportDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(CatalogImportDomainEventHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createCatalogImportDomainEventHandler')
            ->willReturn($stubEventHandler);

        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $catalogImportDomainEvent = new CatalogImportDomainEvent('<xml/>');

        $result = $this->locator->getHandlerFor($catalogImportDomainEvent);

        $this->assertInstanceOf(CatalogImportDomainEventHandler::class, $result);
    }

    public function testRootTemplateChangedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(RootTemplateChangedDomainEventHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createRootTemplateChangedDomainEventHandler')
            ->willReturn($stubEventHandler);

        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $rootTemplateChangedDomainEvent = new RootTemplateChangedDomainEvent('<xml/>');

        $result = $this->locator->getHandlerFor($rootTemplateChangedDomainEvent);

        $this->assertInstanceOf(RootTemplateChangedDomainEventHandler::class, $result);
    }

    public function testImageImportDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ImageImportDomainEventHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createImageImportDomainEventHandler')
            ->willReturn($stubEventHandler);
        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $imagesImportDomainEvent = new ImageImportDomainEvent([]);

        $result = $this->locator->getHandlerFor($imagesImportDomainEvent);

        $this->assertInstanceOf(ImageImportDomainEventHandler::class, $result);
    }

    public function testProductListingSavedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ProductListingSavedDomainEventHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createProductListingSavedDomainEventHandler')
            ->willReturn($stubEventHandler);

        $productListingSavedDomainEvent = new ProductListingSavedDomainEvent('<xml/>');

        $result = $this->locator->getHandlerFor($productListingSavedDomainEvent);

        $this->assertInstanceOf(ProductListingSavedDomainEventHandler::class, $result);
    }

    public function testProductStockQuantityChangedDomainEventHandlerIsLocatedAndReturned()
    {
        $stubEventHandler = $this->getMock(ProductStockQuantityUpdatedDomainEventHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createProductStockQuantityUpdatedDomainEventHandler')
            ->willReturn($stubEventHandler);

        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $stubProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);

        $productStockQuantityChangedDomainEvent = new ProductStockQuantityUpdatedDomainEvent(
            $stubProductId,
            $stubProductStockQuantitySource
        );

        $result = $this->locator->getHandlerFor($productStockQuantityChangedDomainEvent);

        $this->assertInstanceOf(ProductStockQuantityUpdatedDomainEventHandler::class, $result);
    }
}
