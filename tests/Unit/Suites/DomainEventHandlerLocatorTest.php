<?php

namespace Brera;

use Brera\ImageImport\ImportImageDomainEvent;
use Brera\ImageImport\ImportImageDomainEventHandler;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;

/**
 * @covers \Brera\DomainEventHandlerLocator
 * @uses   \Brera\RootTemplateChangedDomainEvent
 * @uses   \Brera\Product\ProductImportDomainEvent
 * @uses   \Brera\Product\CatalogImportDomainEvent
 * @uses   \Brera\ImageImport\ImportImageDomainEvent
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
        $stubProductImportDomainEventHandler = $this->getMock(
            ProductImportDomainEventHandler::class,
            [],
            [],
            '',
            false
        );

        $this->factory->expects($this->once())
            ->method('createProductImportDomainEventHandler')
            ->willReturn($stubProductImportDomainEventHandler);

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
        $stubCatalogImportDomainEventHandler = $this->getMock(CatalogImportDomainEventHandler::class,
            [],
            [],
            '',
            false
        );


        $this->factory->expects($this->once())
            ->method('createCatalogImportDomainEventHandler')
            ->willReturn($stubCatalogImportDomainEventHandler);

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
        $stubRootTemplateChangedDomainEventHandler = $this->getMock(RootTemplateChangedDomainEventHandler::class,
            [],
            [],
            '',
            false
        );


        $this->factory->expects($this->once())
            ->method('createRootTemplateChangedDomainEventHandler')
            ->willReturn($stubRootTemplateChangedDomainEventHandler);

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
    public function itShouldLocateAndReturnImportImageDomainEventHandler()
    {
        $stubImportImageDomainEventHandler = $this->getMock(ImportImageDomainEventHandler::class,
            [],
            [],
            '',
            false
        );

        $this->factory->expects($this->once())
            ->method('createImportImageDomainEventHandler')
            ->willReturn($stubImportImageDomainEventHandler);

        /**
         * The real object has to be used here as getHandlerFor method will call get_class against it
         */
        $ImportImagesDomainEvent = ImportImageDomainEvent::fromArray([]);

        $result = $this->locator->getHandlerFor($ImportImagesDomainEvent);

        $this->assertInstanceOf(ImportImageDomainEventHandler::class, $result);
    }
}
