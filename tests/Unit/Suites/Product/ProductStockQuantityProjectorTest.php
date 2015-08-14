<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionSourceDataTypeException;
use Brera\Projector;
use Brera\SnippetList;
use Brera\SnippetRendererCollection;

/**
 * @covers \Brera\Product\ProductStockQuantityProjector
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
        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);

        $this->projector->project('invalid-projection-source-data', $stubContextSource);
    }

    public function testSnippetListIsWrittenIntoDataPool()
    {
        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $stubSnippetList = $this->getMock(SnippetList::class);
        $stubProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);

        $this->mockSnippetRendererCollection->method('render')->willReturn($stubSnippetList);
        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippetList')->with($stubSnippetList);

        $this->projector->project($stubProductStockQuantitySource, $stubContextSource);
    }
}
