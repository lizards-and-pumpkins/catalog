<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\SampleContextSource;
use Brera\ProjectionSourceData;

/**
 * @covers \Brera\Product\ProductSearchDocumentBuilder
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 */
class ProductSearchDocumentBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SampleContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextSource;

    /**
     * @var ProductSearchDocumentBuilder
     */
    private $searchDocumentBuilder;

    protected function setUp()
    {
        $searchableAttributeCodes = ['name'];

        $this->stubContextSource = $this->getMockBuilder(SampleContextSource::class)
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
        $stubContext = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stubContextSource->expects($this->atLeastOnce())
            ->method('getAllAvailableContexts')
            ->willReturn([$stubContext]);

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
            ->method('getProductForContext')
            ->with($stubContext)
            ->willReturn($stubProduct);

        $result = $this->searchDocumentBuilder->aggregate($stubProductSource, $this->stubContextSource);

        $this->assertInstanceOf(SearchDocumentCollection::class, $result);
    }

    /**
     * @test
     * @expectedException \Brera\InvalidProjectionDataSourceTypeException
     * @expectedExceptionMessage First argument must be instance of ProductSource.
     */
    public function itShouldThrowAnExceptionIfTheDataSourceObjectTypeIsNotProduct()
    {
        $invalidDataSource = $this->getMock(ProjectionSourceData::class);

        $this->searchDocumentBuilder->aggregate($invalidDataSource, $this->stubContextSource);
    }
}
