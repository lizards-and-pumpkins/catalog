<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollectionTest;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRendererCollection;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollection;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingMetaInfoSnippetProjector
 */
class ProductListingMetaInfoSnippetProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetList;

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
     * @var ProductListingMetaInfoSnippetProjector
     */
    private $projector;

    /**
     * @return ProductListingMetaInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProductListingMetaInfoSource()
    {
        return $this->getMock(ProductListingMetaInfo::class, [], [], '', false);
    }

    /**
     * @return ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockContextSource()
    {
        return $this->getMock(ContextSource::class, [], [], '', false);
    }

    protected function setUp()
    {
        $this->stubSnippetList = $this->getMock(SnippetList::class);
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);

        $this->mockRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $this->mockRendererCollection->method('render')->willReturn($this->stubSnippetList);
        
        $this->mockUrlKeyCollector = $this->getMock(
            UrlKeyForContextCollector::class,
            array_merge(get_class_methods(UrlKeyForContextCollector::class), ['collectListingUrlKeys'])
        );

        $this->projector = new ProductListingMetaInfoSnippetProjector(
            $this->mockRendererCollection,
            $this->mockUrlKeyCollector,
            $this->mockDataPoolWriter
        );
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotProduct()
    {
        $stubContextSource = $this->createMockContextSource();
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);
        $this->projector->project('invalid-projection-source-data', $stubContextSource);
    }

    public function testSnippetListIsWrittenToTheDataPool()
    {
        $stubProductListingMetaInfoSource = $this->createMockProductListingMetaInfoSource();
        $stubContextSource = $this->createMockContextSource();
        $stubUrlKeyForContextCollection = $this->getMock(UrlKeyForContextCollection::class, [], [], '', false);
        $this->mockUrlKeyCollector->method('collectListingUrlKeys')->willReturn($stubUrlKeyForContextCollection);

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippetList')->with($this->stubSnippetList);

        $this->projector->project($stubProductListingMetaInfoSource, $stubContextSource);
    }

    public function testUrlKeysForListingsAreCollectedAndWrittenToTheDataPool()
    {
        $stubProductListingMetaInfoSource = $this->createMockProductListingMetaInfoSource();
        $stubContextSource = $this->createMockContextSource();
        $stubUrlKeyForContextCollection = $this->getMock(UrlKeyForContextCollection::class, [], [], '', false);
        
        $this->mockUrlKeyCollector->expects($this->once())->method('collectListingUrlKeys')
            ->with($stubProductListingMetaInfoSource, $stubContextSource)
            ->willReturn($stubUrlKeyForContextCollection);
        
        $this->mockDataPoolWriter->expects($this->once())->method('writeUrlKeyCollection')
            ->with($stubUrlKeyForContextCollection);

        $this->projector->project($stubProductListingMetaInfoSource, $stubContextSource);
    }
}
