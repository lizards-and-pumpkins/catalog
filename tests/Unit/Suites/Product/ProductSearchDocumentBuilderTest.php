<?php

namespace Brera\Product;

use Brera\Environment\Environment;
use Brera\Environment\EnvironmentSource;
use Brera\DataPool\SearchEngine\SearchDocumentBuilder;
use Brera\DataPool\SearchEngine\SearchDocumentCollection;
use Brera\ProjectionSourceData;

/**
 * @covers \Brera\Product\ProductSearchDocumentBuilder
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument
 * @uses   \Brera\DataPool\SearchEngine\SearchDocumentCollection
 * @uses   \Brera\DataPool\SearchEngine\SearchDocumentField
 * @uses   \Brera\DataPool\SearchEngine\SearchDocumentFieldCollection
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
        $searchableAttributeCodes = ['name'];

        $this->stubEnvironmentSource = $this->getMockBuilder(EnvironmentSource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchDocumentBuilder = new ProductSearchDocumentBuilder($searchableAttributeCodes);
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
