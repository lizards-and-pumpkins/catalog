<?php

namespace Brera;

use Brera\Image\ImageImportDomainEvent;
use Brera\Image\ImageImportDomainEventHandler;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;
use Brera\Product\ProductListingSavedDomainEvent;
use Brera\Product\ProductListingSavedDomainEventHandler;

/**
 * @covers \Brera\DomainEventHandlerLocator
 * @uses   \Brera\RootTemplateChangedDomainEvent
 * @uses   \Brera\Product\ProductImportDomainEvent
 * @uses   \Brera\Product\CatalogImportDomainEvent
 * @uses   \Brera\Image\ImageImportDomainEvent
 * @uses   \Brera\Product\ProductListingSavedDomainEvent
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

    /**
     * @test
     * @expectedException \Brera\UnableToFindDomainEventHandlerException
     */
    public function itShouldThrowAnExceptionIfNoHandlerIsLocated()
    {
        /* @var $stubDomainEvent \PHPUnit_Framework_MockObject_MockObject|DomainEvent */
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->locator->getHandlerFor($stubDomainEvent);
    }

    /**
     * @test
     */
    public function itShouldLocateAndReturnProductImportDomainEventHandler()
    {
        $stubDomainEventHandler = $this->getMock(ProductImportDomainEventHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createProductImportDomainEventHandler')
            ->willReturn($stubDomainEventHandler);

        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $productImportDomainEvent = new ProductImportDomainEvent('<xml/>');

        $result = $this->locator->getHandlerFor($productImportDomainEvent);

        $this->assertInstanceOf(ProductImportDomainEventHandler::class, $result);
    }

    /**
     * @test
     */
    public function itShouldLocateAndReturnCatalogImportDomainEventHandler()
    {
        $stubDomainEventHandler = $this->getMock(CatalogImportDomainEventHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createCatalogImportDomainEventHandler')
            ->willReturn($stubDomainEventHandler);

        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $catalogImportDomainEvent = new CatalogImportDomainEvent('<xml/>');

        $result = $this->locator->getHandlerFor($catalogImportDomainEvent);

        $this->assertInstanceOf(CatalogImportDomainEventHandler::class, $result);
    }

    /**
     * @test
     */
    public function itShouldLocateAndReturnRootTemplateChangedDomainEventHandler()
    {
        $stubDomainEventHandler = $this->getMock(RootTemplateChangedDomainEventHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createRootTemplateChangedDomainEventHandler')
            ->willReturn($stubDomainEventHandler);

        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $rootTemplateChangedDomainEvent = new RootTemplateChangedDomainEvent('<xml/>');

        $result = $this->locator->getHandlerFor($rootTemplateChangedDomainEvent);

        $this->assertInstanceOf(RootTemplateChangedDomainEventHandler::class, $result);
    }

    /**
     * @test
     */
    public function itShouldLocateAndReturnImageImportDomainEventHandler()
    {
        $stubDomainEventHandler = $this->getMock(ImageImportDomainEventHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createImageImportDomainEventHandler')
            ->willReturn($stubDomainEventHandler);
        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $imagesImportDomainEvent = new ImageImportDomainEvent([]);

        $result = $this->locator->getHandlerFor($imagesImportDomainEvent);

        $this->assertInstanceOf(ImageImportDomainEventHandler::class, $result);
    }

    /**
     * @test
     */
    public function itShouldLocateAndReturnProductListingSavedDomainEventHandler()
    {
        $stubDomainEventHandler = $this->getMock(ProductListingSavedDomainEventHandler::class, [], [], '', false);

        $this->factory->expects($this->once())
            ->method('createProductListingSavedDomainEventHandler')
            ->willReturn($stubDomainEventHandler);

        $productListingSavedDomainEvent = new ProductListingSavedDomainEvent('<xml/>');

        $result = $this->locator->getHandlerFor($productListingSavedDomainEvent);

        $this->assertInstanceOf(ProductListingSavedDomainEventHandler::class, $result);
    }
}
