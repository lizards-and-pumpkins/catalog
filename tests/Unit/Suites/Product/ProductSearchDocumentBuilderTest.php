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

    /**
     * @var string
     */
    private $searchableAttributeCode = 'foo';

    protected function setUp()
    {
        $this->stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $this->searchDocumentBuilder = new ProductSearchDocumentBuilder([$this->searchableAttributeCode]);
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
        $stubContext = $this->getMock(Context::class, [], [], '', false);
        $this->stubContextSource->expects($this->atLeastOnce())
            ->method('getAllAvailableContexts')
            ->willReturn([$stubContext]);

        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->expects($this->atLeastOnce())
            ->method('getAttributeValue')
            ->with($this->searchableAttributeCode)
            ->willReturn('bar');

        $stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);
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
