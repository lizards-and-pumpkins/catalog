<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionSourceDataTypeException;
use Brera\RootSnippetSourceList;
use Brera\SampleContextSource;
use Brera\SnippetList;
use Brera\SnippetRendererCollection;

/**
 * @covers \Brera\Product\ProductListingTemplateProjector
 */
class ProductListingTemplateProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetRendererCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetRendererCollection;

    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var ProductListingTemplateProjector
     */
    private $projector;

    protected function setUp()
    {
        $this->mockSnippetRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);

        $this->projector = new ProductListingTemplateProjector(
            $this->mockSnippetRendererCollection,
            $this->mockDataPoolWriter
        );
    }

    public function testSnippetListIsWrittenIntoDataPool()
    {
        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $stubProjectionSourceData = $this->getMock(RootSnippetSourceList::class, [], [], '', false);
        $stubSnippetList = $this->getMock(SnippetList::class);

        $this->mockSnippetRendererCollection->method('render')->willReturn($stubSnippetList);
        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippetList')->with($stubSnippetList);

        $this->projector->project($stubProjectionSourceData, $stubContextSource);
    }

    public function testExceptionIsThrownIfProjectionDataIsNotInstanceOfRootSnippetSourceList()
    {
        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);

        $this->projector->project('invalid-projection-source-data', $stubContextSource);
    }
}
