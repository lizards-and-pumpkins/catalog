<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRendererCollection;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollection;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingCriteriaSnippetProjector
 */
class ProductListingCriteriaSnippetProjectorTest extends \PHPUnit_Framework_TestCase
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
     * @var ProductListingCriteriaSnippetProjector
     */
    private $projector;

    /**
     * @return ProductListingCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProductListingCriteria()
    {
        return $this->getMock(ProductListingCriteria::class, [], [], '', false);
    }

    protected function setUp()
    {
        $this->stubSnippetList = $this->getMock(SnippetList::class);
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);

        $this->mockRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $this->mockRendererCollection->method('render')->willReturn($this->stubSnippetList);
        
        $this->mockUrlKeyCollector = $this->getMock(UrlKeyForContextCollector::class, [], [], '', false);

        $this->projector = new ProductListingCriteriaSnippetProjector(
            $this->mockRendererCollection,
            $this->mockUrlKeyCollector,
            $this->mockDataPoolWriter
        );
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotProduct()
    {
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);
        $this->projector->project('invalid-projection-source-data');
    }

    public function testSnippetListIsWrittenToTheDataPool()
    {
        $stubProductListingCriteria = $this->createMockProductListingCriteria();
        $stubUrlKeyForContextCollection = $this->getMock(UrlKeyForContextCollection::class, [], [], '', false);
        $this->mockUrlKeyCollector->method('collectListingUrlKeys')->willReturn($stubUrlKeyForContextCollection);

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippetList')->with($this->stubSnippetList);

        $this->projector->project($stubProductListingCriteria);
    }

    public function testUrlKeysForListingsAreCollectedAndWrittenToTheDataPool()
    {
        $stubProductListingCriteria = $this->createMockProductListingCriteria();
        $stubUrlKeyForContextCollection = $this->getMock(UrlKeyForContextCollection::class, [], [], '', false);
        
        $this->mockUrlKeyCollector->expects($this->once())->method('collectListingUrlKeys')
            ->with($stubProductListingCriteria)
            ->willReturn($stubUrlKeyForContextCollection);
        
        $this->mockDataPoolWriter->expects($this->once())->method('writeUrlKeyCollection')
            ->with($stubUrlKeyForContextCollection);

        $this->projector->project($stubProductListingCriteria);
    }
}
