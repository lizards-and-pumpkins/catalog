<?php

namespace Brera;

use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\CatalogImportDomainEventHandler;
use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;

/**
 * @covers \Brera\DomainEventHandlerLocator
 * @uses \Brera\RootTemplateChangedDomainEvent
 * @uses \Brera\Product\ProductImportDomainEvent
 * @uses \Brera\Product\CatalogImportDomainEvent
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
        $stubDomainEvent = $this->getMock(DomainEvent::class);
        $this->locator->getHandlerFor($stubDomainEvent);
    }

    /**
     * @test
     */
    public function itShouldLocateAndReturnProductImportDomainEventHandler()
    {
        $stubProductImportDomainEventHandler = $this->getMockBuilder(ProductImportDomainEventHandler::class)
        ->disableOriginalConstructor()
        ->getMock();

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
        $stubCatalogImportDomainEventHandler = $this->getMockBuilder(CatalogImportDomainEventHandler::class)
        ->disableOriginalConstructor()
        ->getMock();

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
        $stubRootTemplateChangedDomainEventHandler = $this->getMockBuilder(RootTemplateChangedDomainEventHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

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
}
