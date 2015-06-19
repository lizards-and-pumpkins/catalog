<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\InvalidProjectionDataSourceTypeException;
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

    public function testSearchIndexerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchDocumentBuilder::class, $this->searchDocumentBuilder);
    }

    public function testSearchDocumentCollectionIsReturned()
    {
        $stubContext = $this->getMock(Context::class, [], [], '', false);
        $this->stubContextSource->expects($this->atLeastOnce())
            ->method('getAllAvailableContexts')
            ->willReturn([$stubContext]);

        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($stubProductId);
        $stubProduct->expects($this->atLeastOnce())
            ->method('getAttributeValue')
            ->with('name')
            ->willReturn('bar');

        $stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);
        $stubProductSource->expects($this->atLeastOnce())
            ->method('getProductForContext')
            ->with($stubContext)
            ->willReturn($stubProduct);

        $result = $this->searchDocumentBuilder->aggregate($stubProductSource, $this->stubContextSource);

        $this->assertInstanceOf(SearchDocumentCollection::class, $result);
    }

    public function testExceptionIsThrownIfTheDataSourceObjectTypeIsNotProduct()
    {
        $invalidDataSource = $this->getMock(ProjectionSourceData::class);

        $this->setExpectedException(
            InvalidProjectionDataSourceTypeException::class,
            'First argument must be instance of ProductSource.'
        );

        $this->searchDocumentBuilder->aggregate($invalidDataSource, $this->stubContextSource);
    }
}
