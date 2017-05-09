<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\Import\Product\View\ProductViewLocator;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollection;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\SnippetRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductProjector
 */
class ProductProjectorTest extends TestCase
{
    /**
     * @var ProductProjector
     */
    private $projector;

    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

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

    /**
     * @param ProductView|\PHPUnit_Framework_MockObject_MockObject $productView
     * @param Snippet|\PHPUnit_Framework_MockObject_MockObject $snippet
     * @return SnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSnippetRenderer(ProductView $productView, Snippet $snippet)
    {
        $stubSnippetRenderer = $this->getMockBuilder(SnippetRenderer::class)->setMethods(['render'])->getMock();
        $stubSnippetRenderer->method('render')->with($productView)->willReturn([$snippet]);

        return $stubSnippetRenderer;
    }

    public function setUp()
    {
        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);
        $this->stubSearchDocumentBuilder = $this->createMock(SearchDocumentBuilder::class);

        $stubUrlKeyForContextCollection = $this->createMock(UrlKeyForContextCollection::class);
        $this->stubUrlKeyCollector = $this->createMock(UrlKeyForContextCollector::class);
        $this->stubUrlKeyCollector->method('collectProductUrlKeys')->willReturn($stubUrlKeyForContextCollection);

        $this->productViewLocator = $this->createMock(ProductViewLocator::class);

        $this->projector = new ProductProjector(
            $this->productViewLocator,
            $this->stubSearchDocumentBuilder,
            $this->stubUrlKeyCollector,
            $this->mockDataPoolWriter
        );
    }

    public function testImplementsProjectorInterface()
    {
        $this->assertInstanceOf(Projector::class, $this->projector);
    }

    public function testSnippetsAndSearchDocumentAreSetOnDataPoolWriter()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createMock(Product::class);

        $stubProductView = $this->createMock(ProductView::class);
        $this->productViewLocator->method('createForProduct')->willReturn($stubProductView);

        $stubSnippetA = $this->createMock(Snippet::class);
        $stubSnippetRendererA = $this->createStubSnippetRenderer($stubProductView, $stubSnippetA);

        $stubSnippetB = $this->createMock(Snippet::class);
        $stubSnippetRendererB = $this->createStubSnippetRenderer($stubProductView, $stubSnippetB);

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets')->with($stubSnippetA, $stubSnippetB);

        $stubSearchDocument = $this->createMock(SearchDocument::class);
        $this->stubSearchDocumentBuilder->method('aggregate')->willReturn($stubSearchDocument);

        $this->mockDataPoolWriter->expects($this->once())->method('writeSearchDocument')->with($stubSearchDocument);

        $projector = new ProductProjector(
            $this->productViewLocator,
            $this->stubSearchDocumentBuilder,
            $this->stubUrlKeyCollector,
            $this->mockDataPoolWriter,
            $stubSnippetRendererA,
            $stubSnippetRendererB
        );

        $projector->project($stubProduct);
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
