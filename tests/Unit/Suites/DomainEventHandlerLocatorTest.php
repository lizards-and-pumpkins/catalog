<?php

namespace Brera;

use Brera\Product\ProductImportDomainEvent;
use Brera\Product\ProductImportDomainEventHandler;

/**
 * @covers \Brera\DomainEventHandlerLocator
 * @uses \Brera\Product\ProductImportDomainEvent
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
	 * @expectedException \Brera\UnableToFindDomainEventHandlerException
	 */
	public function itShouldThrowAnExceptionIfNoHandlerIsLocated()
	{
		$stubDomainEvent = $this->getMock(DomainEvent::class);
		$this->locator->getHandlerFor($stubDomainEvent);
	}
}
