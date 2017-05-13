<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollection;
use LizardsAndPumpkins\Import\Projector;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingProjector
 */
class ProductListingProjectorTest extends TestCase
{
    /**
     * @var Projector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetProjector;

    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var UrlKeyForContextCollector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockUrlKeyCollector;

    /**
     * @var ProductListingProjector
     */
    private $projector;

    final protected function setUp()
    {
        $this->mockSnippetProjector = $this->createMock(Projector::class);
        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);
        $this->mockUrlKeyCollector = $this->createMock(UrlKeyForContextCollector::class);

        $this->projector = new ProductListingProjector(
            $this->mockSnippetProjector,
            $this->mockUrlKeyCollector,
            $this->mockDataPoolWriter
        );
    }

    public function testImplementsProjectorInterface()
    {
        $this->assertInstanceOf(Projector::class, $this->projector);
    }

    public function testThrowsAnExceptionIfProjectionSourceDataIsNotProductListing()
    {
        $this->expectException(InvalidProjectionSourceDataTypeException::class);
        $this->projector->project('foo');
    }

    public function testWritesSnippetsToDataPool()
    {
        $dummyProductListing = $this->createMock(ProductListing::class);

        $this->mockSnippetProjector->expects($this->once())->method('project')->with($dummyProductListing);

        $this->projector->project($dummyProductListing);
    }

    public function testUrlKeyCollectionToDataPool()
    {
        $dummyProductListing = $this->createMock(ProductListing::class);

        $dummyUrlKeyForContextCollection = $this->createMock(UrlKeyForContextCollection::class);
        $this->mockUrlKeyCollector->method('collectListingUrlKeys')->willReturn($dummyUrlKeyForContextCollection);

        $this->mockDataPoolWriter->expects($this->once())->method('writeUrlKeyCollection')
            ->with($dummyUrlKeyForContextCollection);

        $this->projector->project($dummyProductListing);
    }
}
