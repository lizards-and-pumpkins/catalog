<?php

namespace LizardsAndPumpkins\Content;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projector;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRendererCollection;

/**
 * @covers \LizardsAndPumpkins\Content\ContentBlockProjector
 */
class ContentBlockProjectorTest extends \PHPUnit_Framework_TestCase
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
     * @var ContentBlockProjector
     */
    private $projector;

    protected function setUp()
    {
        $this->mockSnippetRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);

        $this->projector = new ContentBlockProjector($this->mockSnippetRendererCollection, $this->mockDataPoolWriter);
    }

    public function testProjectorInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Projector::class, $this->projector);
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotAnInstanceOfContentBlockSource()
    {
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);

        $stubProjectionSourceData = 'stub-projection-source-data';

        $this->projector->project($stubProjectionSourceData);
    }

    public function testSnippetListIsWrittenIntoDataPool()
    {
        $stubSnippetList = $this->getMock(SnippetList::class);

        $this->mockSnippetRendererCollection->method('render')->willReturn($stubSnippetList);
        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippetList')->with($stubSnippetList);

        $stubContentBlockSource = $this->getMock(ContentBlockSource::class, [], [], '', false);

        $this->projector->project($stubContentBlockSource);
    }
}
