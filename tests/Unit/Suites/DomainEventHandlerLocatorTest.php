<?php

namespace Brera\PoC;

use Brera\PoC\Product\ProductId;

/**
 * @covers \Brera\PoC\DomainEventHandlerLocator
 * @uses \Brera\PoC\ProductImportDomainEvent
 * @uses \Brera\PoC\ProductCreatedDomainEvent
 */
class DomainEventHandlerLocatorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var DomainEventHandlerLocator
	 */
	private $locator;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $factory;

	protected function setUp()
	{
		$this->factory = $this->getMock(IntegrationTestFactory::class);
		$this->locator = new DomainEventHandlerLocator($this->factory);
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
		 * The real object has to be used here ase getHandlerFor() method will call get_class against it
		 */
		$xml = '<?xml version="1.0"?><rootNode></rootNode>';
		$productImportDomainEvent = new ProductImportDomainEvent($xml);

		$result = $this->locator->getHandlerFor($productImportDomainEvent);

		$this->assertInstanceOf(ProductImportDomainEventHandler::class, $result);
	}

	/**
	 * @test
	 */
	public function itShouldLocateAndReturnProductCreatedDomainEventHandler()
	{
		$stubProductCreatedDomainEventHandler = $this->getMockBuilder(ProductCreatedDomainEventHandler::class)
		                                            ->disableOriginalConstructor()
		                                            ->getMock();

		$this->factory->expects($this->once())
		              ->method('createProductCreatedDomainEventHandler')
		              ->willReturn($stubProductCreatedDomainEventHandler);

		/**
		 * The real object has to be used here ase getHandlerFor() method will call get_class against it
		 */
		$stubProductId = $this->getMockBuilder(ProductId::class)
			->disableOriginalConstructor()
			->getMock();
		$productCreatedDomainEvent = new ProductCreatedDomainEvent($stubProductId);

		$result = $this->locator->getHandlerFor($productCreatedDomainEvent);

		$this->assertInstanceOf(ProductCreatedDomainEventHandler::class, $result);
	}

	/**
	 * @test
	 * @expectedException \Brera\PoC\UnableToFindDomainEventHandlerException
	 */
	public function itShouldThrowAnExceptionIfNoHandlerIsLocated()
	{
		$stubDomainEvent = $this->getMock(DomainEvent::class);
		$this->locator->getHandlerFor($stubDomainEvent);
	}
}
