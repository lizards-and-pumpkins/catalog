<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetRendererCollection;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollection;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingCriteriaSnippetProjector
 */
class ProductListingCriteriaSnippetProjectorTest extends \PHPUnit_Framework_TestCase
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
        $this->stubSnippet = $this->getMock(Snippet::class, [], [], '', false);
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);

        $this->mockRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $this->mockRendererCollection->method('render')->willReturn([$this->stubSnippet]);
        
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

    public function testSnippetIsWrittenToTheDataPool()
    {
        $stubProductListingCriteria = $this->createMockProductListingCriteria();
        $stubUrlKeyForContextCollection = $this->getMock(UrlKeyForContextCollection::class, [], [], '', false);
        $this->mockUrlKeyCollector->method('collectListingUrlKeys')->willReturn($stubUrlKeyForContextCollection);

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets')->with($this->stubSnippet);

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
