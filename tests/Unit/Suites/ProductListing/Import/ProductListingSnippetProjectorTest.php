<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollection;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\SnippetRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetProjector
 */
class ProductListingSnippetProjectorTest extends TestCase
{
    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var UrlKeyForContextCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockUrlKeyCollector;

    /**
     * @param ProductListing|\PHPUnit_Framework_MockObject_MockObject $productListing
     * @param Snippet|\PHPUnit_Framework_MockObject_MockObject $snippet
     * @return SnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSnippetRenderer(ProductListing $productListing, Snippet $snippet)
    {
        $stubSnippetRenderer = $this->getMockBuilder(SnippetRenderer::class)->setMethods(['render'])->getMock();
        $stubSnippetRenderer->method('render')->with($productListing)->willReturn([$snippet]);

        return $stubSnippetRenderer;
    }

    final protected function setUp()
    {
        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);
        $this->mockUrlKeyCollector = $this->createMock(UrlKeyForContextCollector::class);
    }

    public function testImplementsProjectorInterface()
    {
        $projector = new ProductListingSnippetProjector($this->mockUrlKeyCollector, $this->mockDataPoolWriter);
        $this->assertInstanceOf(Projector::class, $projector);
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotProduct()
    {
        $this->expectException(\TypeError::class);
        (new ProductListingSnippetProjector($this->mockUrlKeyCollector, $this->mockDataPoolWriter))->project('foo');
    }

    public function testSnippetIsWrittenToTheDataPool()
    {
        $stubProductListing = $this->createMock(ProductListing::class);

        $stubSnippetA = $this->createMock(Snippet::class);
        $stubSnippetRendererA = $this->createStubSnippetRenderer($stubProductListing, $stubSnippetA);

        $stubSnippetB = $this->createMock(Snippet::class);
        $stubSnippetRendererB = $this->createStubSnippetRenderer($stubProductListing, $stubSnippetB);

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets')->with($stubSnippetA, $stubSnippetB);

        $stubUrlKeyForContextCollection = $this->createMock(UrlKeyForContextCollection::class);
        $this->mockUrlKeyCollector->method('collectListingUrlKeys')->willReturn($stubUrlKeyForContextCollection);

        $projector = new ProductListingSnippetProjector(
            $this->mockUrlKeyCollector,
            $this->mockDataPoolWriter,
            $stubSnippetRendererA,
            $stubSnippetRendererB
        );
        $projector->project($stubProductListing);
    }

    public function testUrlKeysForListingsAreCollectedAndWrittenToTheDataPool()
    {
        $stubProductListing = $this->createMock(ProductListing::class);
        $stubUrlKeyForContextCollection = $this->createMock(UrlKeyForContextCollection::class);
        
        $this->mockUrlKeyCollector->expects($this->once())->method('collectListingUrlKeys')->with($stubProductListing)
            ->willReturn($stubUrlKeyForContextCollection);
        
        $this->mockDataPoolWriter->expects($this->once())->method('writeUrlKeyCollection')
            ->with($stubUrlKeyForContextCollection);

        $projector = new ProductListingSnippetProjector($this->mockUrlKeyCollector, $this->mockDataPoolWriter);
        $projector->project($stubProductListing);
    }
}
