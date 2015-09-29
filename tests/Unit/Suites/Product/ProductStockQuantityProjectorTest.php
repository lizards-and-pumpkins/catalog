<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projector;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRendererCollection;

/**
 * @covers \LizardsAndPumpkins\Product\ProductStockQuantityProjector
 */
class ProductStockQuantityProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var SnippetRendererCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetRendererCollection;

    /**
     * @var ProductStockQuantityProjector
     */
    private $projector;

    protected function setUp()
    {
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);
        $this->mockSnippetRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $this->projector = new ProductStockQuantityProjector(
            $this->mockDataPoolWriter,
            $this->mockSnippetRendererCollection
        );
    }

    public function testProjectorInterfaceShouldBeImplemented()
    {
        $this->assertInstanceOf(Projector::class, $this->projector);
    }

    public function testExceptionIsThrownIfProjectionDataIsNotInstanceOfProductStockQuantitySource()
    {
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);

        $this->projector->project('invalid-projection-source-data');
    }

    public function testSnippetListIsWrittenIntoDataPool()
    {
        $stubSnippetList = $this->getMock(SnippetList::class);
        $stubProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);

        $this->mockSnippetRendererCollection->method('render')->willReturn($stubSnippetList);
        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippetList')->with($stubSnippetList);

        $this->projector->project($stubProductStockQuantitySource);
    }
}
