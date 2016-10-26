<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\Import\Product\View\ProductViewLocator;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollection;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\SnippetRendererCollection;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductProjector
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
     * @var SearchDocument|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocument;

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
        $this->stubSnippet = $this->createMock(Snippet::class);
        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);
        $this->stubSearchDocument = $this->createMock(SearchDocument::class);

        $this->mockRendererCollection = $this->createMock(SnippetRendererCollection::class);
        $this->mockRendererCollection->method('render')->willReturn([$this->stubSnippet]);

        $this->stubSearchDocumentBuilder = $this->createMock(SearchDocumentBuilder::class);
        $this->stubSearchDocumentBuilder->method('aggregate')->willReturn($this->stubSearchDocument);

        $stubUrlKeyForContextCollection = $this->createMock(UrlKeyForContextCollection::class);
        $this->stubUrlKeyCollector = $this->createMock(UrlKeyForContextCollector::class);
        $this->stubUrlKeyCollector->method('collectProductUrlKeys')->willReturn($stubUrlKeyForContextCollection);

        $stubProductView = $this->createMock(ProductView::class);
        $this->productViewLocator = $this->createMock(ProductViewLocator::class);
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
        $stubProduct = $this->createMock(Product::class);

        $this->projector->project($stubProduct);
    }

    public function testSnippetsAndSearchDocumentAreSetOnDataPoolWriter()
    {
        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets')->with($this->stubSnippet);
        $this->mockDataPoolWriter->expects($this->once())->method('writeSearchDocument')
            ->with($this->stubSearchDocument);

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createMock(Product::class);

        $this->projector->project($stubProduct);
    }

    public function testItWritesTheUrlKeyCollectionForTheDataVersionToTheDataPool()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createMock(Product::class);

        $urlKeyCollection = $this->stubUrlKeyCollector->collectProductUrlKeys($stubProduct);
        $this->mockDataPoolWriter->expects($this->once())->method('writeUrlKeyCollection')->with($urlKeyCollection);

        $this->projector->project($stubProduct);
    }

    public function testItDelegatesToTheUrlKeyCollectorToCollectAllKeys()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createMock(Product::class);

        $this->stubUrlKeyCollector->expects($this->once())->method('collectProductUrlKeys')
            ->willReturn($this->createMock(UrlKeyForContextCollection::class));

        $this->projector->project($stubProduct);
    }
}
