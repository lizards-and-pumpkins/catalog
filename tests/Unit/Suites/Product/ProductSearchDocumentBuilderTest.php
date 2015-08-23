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

    /**
     * @var string
     */
    private $searchableAttributeCode = 'foo';

    protected function setUp()
    {
        $this->stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $this->searchDocumentBuilder = new ProductSearchDocumentBuilder([$this->searchableAttributeCode]);
    }

    public function testSearchIndexerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchDocumentBuilder::class, $this->searchDocumentBuilder);
    }

    public function testSearchDocumentCollectionIsReturned()
    {
        $stubContext = $this->getMock(Context::class, [], [], '', false);
        $this->stubContextSource->method('getAllAvailableContexts')->willReturn([$stubContext]);

        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->method('getFirstValueOfAttribute')->with($this->searchableAttributeCode)->willReturn('bar');

        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $stubProduct->method('getId')->willReturn($stubProductId);

        /** @var ProductSource|\PHPUnit_Framework_MockObject_MockObject $stubProductSource */
        $stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);
        $stubProductSource->method('getProductForContext')->with($stubContext)->willReturn($stubProduct);

        $result = $this->searchDocumentBuilder->aggregate($stubProductSource, $this->stubContextSource);

        $this->assertInstanceOf(SearchDocumentCollection::class, $result);
    }

    public function testExceptionIsThrownIfTheDataSourceObjectTypeIsNotProduct()
    {
        /** @var ProjectionSourceData|\PHPUnit_Framework_MockObject_MockObject $invalidDataSource */
        $invalidDataSource = $this->getMock(ProjectionSourceData::class);

        $this->setExpectedException(
            InvalidProjectionDataSourceTypeException::class,
            'First argument must be instance of ProductSource.'
        );

        $this->searchDocumentBuilder->aggregate($invalidDataSource, $this->stubContextSource);
    }
}
