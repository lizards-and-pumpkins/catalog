<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\SnippetRendererCollection;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollection;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetProjector
 */
class ProductListingSnippetProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Snippet|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippet;

    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var SnippetRendererCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRendererCollection;

    /**
     * @var UrlKeyForContextCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockUrlKeyCollector;

    /**
     * @var ProductListingSnippetProjector
     */
    private $projector;

    /**
     * @return ProductListing|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProductListing() : ProductListing
    {
        return $this->createMock(ProductListing::class);
    }

    protected function setUp()
    {
        $this->stubSnippet = $this->createMock(Snippet::class);
        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);

        $this->mockRendererCollection = $this->createMock(SnippetRendererCollection::class);
        $this->mockRendererCollection->method('render')->willReturn([$this->stubSnippet]);
        
        $this->mockUrlKeyCollector = $this->createMock(UrlKeyForContextCollector::class);

        $this->projector = new ProductListingSnippetProjector(
            $this->mockRendererCollection,
            $this->mockUrlKeyCollector,
            $this->mockDataPoolWriter
        );
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotProduct()
    {
        $this->expectException(InvalidProjectionSourceDataTypeException::class);
        $this->projector->project('invalid-projection-source-data');
    }

    public function testSnippetIsWrittenToTheDataPool()
    {
        $stubProductListing = $this->createMockProductListing();
        $stubUrlKeyForContextCollection = $this->createMock(UrlKeyForContextCollection::class);
        $this->mockUrlKeyCollector->method('collectListingUrlKeys')->willReturn($stubUrlKeyForContextCollection);

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets')->with($this->stubSnippet);

        $this->projector->project($stubProductListing);
    }

    public function testUrlKeysForListingsAreCollectedAndWrittenToTheDataPool()
    {
        $stubProductListing = $this->createMockProductListing();
        $stubUrlKeyForContextCollection = $this->createMock(UrlKeyForContextCollection::class);
        
        $this->mockUrlKeyCollector->expects($this->once())->method('collectListingUrlKeys')->with($stubProductListing)
            ->willReturn($stubUrlKeyForContextCollection);
        
        $this->mockDataPoolWriter->expects($this->once())->method('writeUrlKeyCollection')
            ->with($stubUrlKeyForContextCollection);

        $this->projector->project($stubProductListing);
    }
}
