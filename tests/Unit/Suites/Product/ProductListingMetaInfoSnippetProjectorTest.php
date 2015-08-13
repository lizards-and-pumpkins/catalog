<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\ProjectionSourceData;
use Brera\SnippetList;
use Brera\SnippetRendererCollection;

/**
 * @covers \Brera\Product\ProductListingMetaInfoSnippetProjector
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
     * @var ProductListingMetaInfoSnippetProjector
     */
    private $projector;

    protected function setUp()
    {
        $this->stubSnippetList = $this->getMock(SnippetList::class);
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);

        $this->mockRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $this->mockRendererCollection->method('render')->willReturn($this->stubSnippetList);

        $this->projector = new ProductListingMetaInfoSnippetProjector(
            $this->mockRendererCollection,
            $this->mockDataPoolWriter
        );
    }

    public function testExceptionIsThrownIfTheDataSourceTypeIsNotProduct()
    {
        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);

        /** @var ProjectionSourceData|\PHPUnit_Framework_MockObject_MockObject $invalidDataSourceType */
        $invalidDataSourceType = $this->getMock(ProjectionSourceData::class);

        $this->setExpectedException(InvalidProjectionDataSourceTypeException::class);

        $this->projector->project($invalidDataSourceType, $stubContextSource);
    }

    public function testSnippetListIsWrittenIntoDataPoolWriter()
    {
        /**
         * @var ProductListingMetaInfoSource|\PHPUnit_Framework_MockObject_MockObject $stubProductListingMetaInfoSource
         */
        $stubProductListingMetaInfoSource = $this->getMock(ProductListingMetaInfoSource::class, [], [], '', false);

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);

        $this->mockDataPoolWriter->expects($this->once())
            ->method('writeSnippetList')
            ->with($this->stubSnippetList);

        $this->projector->project($stubProductListingMetaInfoSource, $stubContextSource);
    }
}
