<?php

namespace Brera\Product;

use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\ProjectionSourceData;
use Brera\SampleContextSource;
use Brera\SnippetList;
use Brera\SnippetRendererCollection;

/**
 * @covers \Brera\Product\ProductListingProjector
 */
class ProductListingProjectorTest extends \PHPUnit_Framework_TestCase
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
     * @var ProductListingProjector
     */
    private $projector;

    protected function setUp()
    {
        $this->stubSnippetList = $this->getMock(SnippetList::class);
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);

        $this->mockRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $this->mockRendererCollection->method('render')->willReturn($this->stubSnippetList);

        $this->projector = new ProductListingProjector(
            $this->mockRendererCollection,
            $this->mockDataPoolWriter
        );
    }

    public function testExceptionIsThrownIfTheDataSourceTypeIsNotProduct()
    {
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $invalidDataSourceType = $this->getMock(ProjectionSourceData::class);

        $this->setExpectedException(InvalidProjectionDataSourceTypeException::class);

        $this->projector->project($invalidDataSourceType, $stubContextSource);
    }

    public function testSnippetListIsWrittenIntoDataPoolWriter()
    {
        $stubProductListingSource = $this->getMock(ProductListingSource::class, [], [], '', false);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $this->mockDataPoolWriter->expects($this->once())
            ->method('writeSnippetList')
            ->with($this->stubSnippetList);

        $this->projector->project($stubProductListingSource, $stubContextSource);
    }
}
