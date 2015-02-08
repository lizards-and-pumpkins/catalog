<?php

namespace Brera;

use Brera\KeyValue\DataPoolWriter;
use Brera\Queue\InMemory\InMemoryQueue;
use Brera\Product\ProductBuilder;

/**
 * @covers \Brera\IntegrationTestFactory
 * @uses \Brera\KeyValue\DataPoolWriter
 * @uses \Brera\Queue\InMemory\InMemoryQueue
 * @uses \Brera\Product\ProductBuilder
 * @uses \Brera\DataVersion
 * @uses \Brera\Environment\EnvironmentBuilder
 * @uses \Brera\DomainEventHandlerLocator
 */
class IntegrationTestFactoryTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var IntegrationTestFactory
	 */
	private $factory;

	protected function setUp()
	{
		$this->factory = new IntegrationTestFactory();
	}

	/**
	 * @test
	 */
	public function itShouldCreateDataPoolWriter()
	{
		$result = $this->factory->createDataPoolWriter();

		$this->assertInstanceOf(DataPoolWriter::class, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnInMemoryQueue()
	{
		$result = $this->factory->getEventQueue();

		$this->assertInstanceOf(InMemoryQueue::class, $result);
	}

	/**
	 * @test
	 */
	public function itShouldCreateSnippetResultList()
	{
		$result = $this->factory->createSnippetResultList();

		$this->assertInstanceOf(SnippetResultList::class, $result);
	}

	/**
	 * @test
	 */
	public function itShouldCreateSnippetKeyGenerator()
	{
		$result = $this->factory->createProductDetailViewSnippetKeyGenerator();

		$this->assertInstanceOf(SnippetKeyGenerator::class, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnProductBuilder()
	{
		$result = $this->factory->getProductBuilder();

		$this->assertInstanceOf(ProductBuilder::class, $result);
	}

	/**
	 * @test
	 */
	public function isShouldCreateDomainEventHandlerLocator()
	{
		$result = $this->factory->createDomainEventHandlerLocator();

		$this->assertInstanceOf(DomainEventHandlerLocator::class, $result);
	}
}
