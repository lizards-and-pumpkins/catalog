<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\Import\Product\View\ProductViewLocator;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollection;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;
use LizardsAndPumpkins\Import\Projector;
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
     * @var Projector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetProjector;

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

    public function setUp()
    {
        $this->mockSnippetProjector = $this->createMock(Projector::class);
        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);
        $this->stubSearchDocumentBuilder = $this->createMock(SearchDocumentBuilder::class);

        $stubUrlKeyForContextCollection = $this->createMock(UrlKeyForContextCollection::class);
        $this->stubUrlKeyCollector = $this->createMock(UrlKeyForContextCollector::class);
        $this->stubUrlKeyCollector->method('collectProductUrlKeys')->willReturn($stubUrlKeyForContextCollection);

        $this->productViewLocator = $this->createMock(ProductViewLocator::class);

        $this->projector = new ProductProjector(
            $this->productViewLocator,
            $this->mockSnippetProjector,
            $this->stubSearchDocumentBuilder,
            $this->stubUrlKeyCollector,
            $this->mockDataPoolWriter
        );
    }

    public function testImplementsProjectorInterface()
    {
        $this->assertInstanceOf(Projector::class, $this->projector);
    }

    public function testThrownAnExceptionIfProjectionSourceDataIsNotProduct()
    {
        $this->expectException(InvalidProjectionSourceDataTypeException::class);
        $this->projector->project('foo');
    }

    public function testWritesSearchDocumentToDataPool()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $dummyProduct */
        $dummyProduct = $this->createMock(Product::class);

        $dummyProductView = $this->createMock(ProductView::class);
        $this->productViewLocator->method('createForProduct')->willReturn($dummyProductView);

        $dummySearchDocument = $this->createMock(SearchDocument::class);
        $this->stubSearchDocumentBuilder->method('aggregate')->willReturn($dummySearchDocument);

        $this->mockDataPoolWriter->expects($this->once())->method('writeSearchDocument')->with($dummySearchDocument);

        $this->projector->project($dummyProduct);
    }

    public function testTriggersSnippetProjection()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $dummyProduct */
        $dummyProduct = $this->createMock(Product::class);

        $dummyProductView = $this->createMock(ProductView::class);
        $this->productViewLocator->method('createForProduct')->willReturn($dummyProductView);

        $this->mockSnippetProjector->expects($this->once())->method('project')->with($dummyProductView);

        $this->projector->project($dummyProduct);
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
