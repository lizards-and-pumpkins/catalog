<?php

namespace Brera\Product;

use Brera\Environment\EnvironmentSource;
use Brera\KeyValue\DataPoolWriter;
use Brera\SnippetResultList;
use Brera\SnippetRendererCollection;
use Brera\ProjectionSourceData;

/**
 * @covers \Brera\Product\ProductProjector
 * @uses \Brera\Product\ProductSnippetRendererCollection
 */
class ProductProjectorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var ProductProjector
	 */
	private $projector;

	/**
	 * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubSnippetResultList;

	/**
	 * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubDataPoolWriter;

	/**
	 * @var SnippetRendererCollection|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubProductSnippetRendererCollection;

	public function setUp()
	{
		$this->stubSnippetResultList = $this->getMock(SnippetResultList::class);
		$this->stubDataPoolWriter = $this->getMockBuilder(DataPoolWriter::class)
			->setMethods(['writeSnippetResultList'])
			->disableOriginalConstructor()
			->getMock();
		$this->stubProductSnippetRendererCollection = $this->getMockBuilder(
			ProductSnippetRendererCollection::class
		)->getMockForAbstractClass();
		$this->stubProductSnippetRendererCollection->expects($this->any())
			->method('getSnippetResultList')
			->willReturn($this->stubSnippetResultList);

		$this->projector = new ProductProjector(
			$this->stubProductSnippetRendererCollection,
			$this->stubDataPoolWriter
		);
	}

	/**
	 * @test
	 */
	public function itShouldSetSnippetResultListOnDataPoolWriter()
	{
		$this->stubDataPoolWriter->expects($this->once())
			->method('writeSnippetResultList')
			->with($this->stubSnippetResultList);

		$stubProduct = $this->getMockBuilder(ProductSource::class)
			->disableOriginalConstructor()
			->getMock();

		$stubEnvironment = $this->getMockBuilder(EnvironmentSource::class)
			->disableOriginalConstructor()
			->getMock();

		$this->projector->project($stubProduct, $stubEnvironment);
	}

	/**
	 * @test
	 * @expectedException \Brera\InvalidProjectionDataSourceType
	 */
	public function itShouldThrowIfTheDataSourceTypeIsNotProduct()
	{
		$stubEnvironment = $this->getMockBuilder(EnvironmentSource::class)
			->disableOriginalConstructor()
			->getMock();
		$invalidDataSourceType = $this->getMock(ProjectionSourceData::class);

		$this->projector->project($invalidDataSourceType, $stubEnvironment);
	}
}
