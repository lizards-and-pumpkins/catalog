<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollection;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRendererCollection;

/**
 * @covers \LizardsAndPumpkins\Product\ProductProjector
 */
class ProductProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductProjector
     */
    private $projector;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetList;

    /**
     * @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocumentCollection;

    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var SnippetRendererCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRendererCollection;

    /**
     * @var ProductSearchDocumentBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocumentBuilder;

    /**
     * @var UrlKeyForContextCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubUrlKeyCollector;

    public function setUp()
    {
        $this->stubSnippetList = $this->getMock(SnippetList::class);
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);
        $this->stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);

        $this->mockRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $this->mockRendererCollection->method('render')->willReturn($this->stubSnippetList);

        $this->stubSearchDocumentBuilder = $this->getMock(ProductSearchDocumentBuilder::class, [], [], '', false);

        $this->stubUrlKeyCollector = $this->getMock(UrlKeyForContextCollector::class, [], [], '', false);
        $this->stubUrlKeyCollector->method('collectProductUrlKeys')->willReturn(
            $this->getMock(UrlKeyForContextCollection::class, [], [], '', false)
        );

        $this->projector = new ProductProjector(
            $this->mockRendererCollection,
            $this->stubSearchDocumentBuilder,
            $this->stubUrlKeyCollector,
            $this->mockDataPoolWriter
        );
    }

    public function testSnippetListAndSearchDocumentAreSetOnDataPoolWriter()
    {
        $this->stubSearchDocumentBuilder->expects($this->once())
            ->method('aggregate')
            ->willReturn($this->stubSearchDocumentCollection);

        $this->mockDataPoolWriter->expects($this->once())
            ->method('writeSnippetList')
            ->with($this->stubSnippetList);

        $this->mockDataPoolWriter->expects($this->once())
            ->method('writeSearchDocumentCollection')
            ->with($this->stubSearchDocumentCollection);

        $stubProduct = $this->getMock(Product::class, [], [], '', false);

        $this->projector->project($stubProduct);
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotProduct()
    {
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);

        $this->projector->project('invalid-projection-source-data');
    }

    public function testItWritesTheUrlKeyCollectionForTheDataVersionToTheDataPool()
    {
        $this->stubSearchDocumentBuilder->method('aggregate')->willReturn($this->stubSearchDocumentCollection);

        $stubProduct = $this->getMock(Product::class, [], [], '', false);

        $urlKeyCollection = $this->stubUrlKeyCollector->collectProductUrlKeys($stubProduct);
        $this->mockDataPoolWriter->expects($this->once())->method('writeUrlKeyCollection')->with($urlKeyCollection);

        $this->projector->project($stubProduct);
    }

    public function testItDelegatesToTheUrlKeyCollectorToCollectAllKeys()
    {
        $this->stubSearchDocumentBuilder->method('aggregate')->willReturn($this->stubSearchDocumentCollection);

        $stubProduct = $this->getMock(Product::class, [], [], '', false);

        $this->stubUrlKeyCollector->expects($this->once())->method('collectProductUrlKeys')
            ->with($stubProduct);

        $this->projector->project($stubProduct);
    }
}
