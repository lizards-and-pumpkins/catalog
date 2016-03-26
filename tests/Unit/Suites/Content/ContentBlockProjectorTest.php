<?php

namespace LizardsAndPumpkins\Content;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockProjector;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource;
use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\SnippetRendererCollection;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockProjector
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
        $this->expectException(InvalidProjectionSourceDataTypeException::class);

        $stubProjectionSourceData = 'stub-projection-source-data';

        $this->projector->project($stubProjectionSourceData);
    }

    public function testSnippetIsWrittenIntoDataPool()
    {
        $stubSnippet = $this->getMock(Snippet::class, [], [], '', false);

        $this->mockSnippetRendererCollection->method('render')->willReturn([$stubSnippet]);
        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets')->with($stubSnippet);

        $stubContentBlockSource = $this->getMock(ContentBlockSource::class, [], [], '', false);

        $this->projector->project($stubContentBlockSource);
    }
}
