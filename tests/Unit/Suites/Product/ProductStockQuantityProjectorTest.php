<?php

namespace Brera\Product;

use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\ProjectionSourceData;
use Brera\Projector;
use Brera\SampleContextSource;
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
        $stubProjectionSourceData = $this->getMock(ProjectionSourceData::class);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $this->setExpectedException(InvalidProjectionDataSourceTypeException::class);

        $this->projector->project($stubProjectionSourceData, $stubContextSource);
    }

    public function testSnippetListIsWrittenDataPool()
    {
        $stubSnippetList = $this->getMock(SnippetList::class);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $this->mockSnippetRendererCollection->method('render')
            ->willReturn($stubSnippetList);

        $this->mockDataPoolWriter->expects($this->once())
            ->method('writeSnippetList')
            ->with($stubSnippetList);

        $stubProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);
        $this->projector->project($stubProductStockQuantitySource, $stubContextSource);
    }
}
