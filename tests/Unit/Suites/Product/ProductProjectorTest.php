<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\Projection\Catalog\ProductViewLocator;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollection;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;
use LizardsAndPumpkins\Snippet;
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
     * @var Snippet|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippet;

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
     * @var SearchDocumentBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocumentBuilder;

    /**
     * @var UrlKeyForContextCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubUrlKeyCollector;

    /**
     * @var ProductViewLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productViewLocator;

    public function setUp()
    {
        $this->stubSnippet = $this->getMock(Snippet::class, [], [], '', false);
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);
        $this->stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);

        $this->mockRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $this->mockRendererCollection->method('render')->willReturn([$this->stubSnippet]);

        $this->stubSearchDocumentBuilder = $this->getMock(SearchDocumentBuilder::class);
        $this->stubSearchDocumentBuilder->method('aggregate')->willReturn($this->stubSearchDocumentCollection);

        $stubUrlKeyForContextCollection = $this->getMock(UrlKeyForContextCollection::class, [], [], '', false);
        $this->stubUrlKeyCollector = $this->getMock(UrlKeyForContextCollector::class, [], [], '', false);
        $this->stubUrlKeyCollector->method('collectProductUrlKeys')->willReturn($stubUrlKeyForContextCollection);

        $stubProductView = $this->getMock(ProductView::class);
        $this->productViewLocator = $this->getMock(ProductViewLocator::class, [], [], '', false);
        $this->productViewLocator->method('createForProduct')->willReturn($stubProductView);

        $this->projector = new ProductProjector(
            $this->productViewLocator,
            $this->mockRendererCollection,
            $this->stubSearchDocumentBuilder,
            $this->stubUrlKeyCollector,
            $this->mockDataPoolWriter
        );
    }

    public function testProductViewLocatorIsCalled()
    {
        $this->productViewLocator->expects($this->once())->method('createForProduct');

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class);

        $this->projector->project($stubProduct);
    }

    public function testSnippetsAndSearchDocumentAreSetOnDataPoolWriter()
    {
        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets')->with($this->stubSnippet);
        $this->mockDataPoolWriter->expects($this->once())->method('writeSearchDocumentCollection')
            ->with($this->stubSearchDocumentCollection);

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class);

        $this->projector->project($stubProduct);
    }

    public function testItWritesTheUrlKeyCollectionForTheDataVersionToTheDataPool()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class);

        $urlKeyCollection = $this->stubUrlKeyCollector->collectProductUrlKeys($stubProduct);
        $this->mockDataPoolWriter->expects($this->once())->method('writeUrlKeyCollection')->with($urlKeyCollection);

        $this->projector->project($stubProduct);
    }

    public function testItDelegatesToTheUrlKeyCollectorToCollectAllKeys()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class);

        $this->stubUrlKeyCollector->expects($this->once())->method('collectProductUrlKeys')
            ->willReturn($this->getMock(UrlKeyForContextCollection::class, [], [], '', false));

        $this->projector->project($stubProduct);
    }
}
