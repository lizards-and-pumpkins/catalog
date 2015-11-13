<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 */
class ProductSearchDocumentBuilderTest extends \PHPUnit_Framework_TestCase
{
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
        $this->searchDocumentBuilder = new ProductSearchDocumentBuilder([$this->searchableAttributeCode]);
    }

    public function testSearchIndexerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchDocumentBuilder::class, $this->searchDocumentBuilder);
    }

    public function testSearchDocumentCollectionIsReturned()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('getAllValuesOfAttribute')->with($this->searchableAttributeCode)->willReturn(['bar']);
        $stubProduct->method('getContext')->willReturn($this->getMock(Context::class, [], [], '', false));
        $stubProduct->method('getId')->willReturn($this->getMock(ProductId::class, [], [], '', false));

        $result = $this->searchDocumentBuilder->aggregate($stubProduct);

        $this->assertInstanceOf(SearchDocumentCollection::class, $result);
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotProduct()
    {
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);
        $this->searchDocumentBuilder->aggregate('invalid-projection-source-data');
    }
}
