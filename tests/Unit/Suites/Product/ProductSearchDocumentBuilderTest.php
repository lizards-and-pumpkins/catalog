<?php

namespace Brera\Product;

use Brera\Environment\Environment;
use Brera\Environment\EnvironmentSource;
use Brera\KeyValue\SearchDocumentBuilder;
use Brera\KeyValue\SearchDocumentCollection;
use Brera\ProjectionSourceData;

/**
 * @covers \Brera\Product\ProductSearchDocumentBuilder
 * @uses   \Brera\KeyValue\SearchDocument
 * @uses   \Brera\KeyValue\SearchDocumentCollection
 * @uses   \Brera\KeyValue\SearchDocumentField
 * @uses   \Brera\KeyValue\SearchDocumentFieldCollection
 */
class ProductSearchDocumentBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EnvironmentSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubEnvironmentSource;

    /**
     * @var ProductSearchDocumentBuilder
     */
    private $searchDocumentBuilder;

    protected function setUp()
    {
        $this->stubEnvironmentSource = $this->getMockBuilder(EnvironmentSource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchDocumentBuilder = new ProductSearchDocumentBuilder(['name']);
    }

    /**
     * @test
     */
    public function itShouldImplementSearchIndexer()
    {
        $this->assertInstanceOf(SearchDocumentBuilder::class, $this->searchDocumentBuilder);
    }

    /**
     * @test
     */
    public function itShouldReturnSearchDocumentCollection()
    {
        $stubEnvironment = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stubEnvironmentSource->expects($this->atLeastOnce())
            ->method('extractEnvironments')
            ->willReturn([$stubEnvironment]);

        $stubProductId = $this->getMockBuilder(ProductId::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stubProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubProduct->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($stubProductId);
        $stubProduct->expects($this->atLeastOnce())
            ->method('getAttributeValue')
            ->with('name')
            ->willReturn('bar');

        $stubProductSource = $this->getMockBuilder(ProductSource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubProductSource->expects($this->atLeastOnce())
            ->method('getProductForEnvironment')
            ->with($stubEnvironment)
            ->willReturn($stubProduct);

        $result = $this->searchDocumentBuilder->aggregate($stubProductSource, $this->stubEnvironmentSource);

        $this->assertInstanceOf(SearchDocumentCollection::class, $result);
    }

    /**
     * @test
     * @expectedException \Brera\InvalidProjectionDataSourceType
     */
    public function itShouldThrowAnExceptionIfTheDataSourceObjectTypeIsNotProduct()
    {
        $invalidDataSource = $this->getMock(ProjectionSourceData::class);

        $this->searchDocumentBuilder->aggregate($invalidDataSource, $this->stubEnvironmentSource);
    }
}
