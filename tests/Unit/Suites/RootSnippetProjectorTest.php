<?php

namespace Brera;

use Brera\DataPool\DataPoolWriter;

/**
 * @covers \Brera\RootSnippetProjector
 */
class RootSnippetProjectorTest extends \PHPUnit_Framework_TestCase
{
    public function testSnippetListIsWrittenIntoDataPool()
    {
        $stubDataObject = $this->getMock(ProjectionSourceData::class);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $stubSnippetList = $this->getMock(SnippetList::class);

        $mockSnippetRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $mockSnippetRendererCollection->expects($this->once())
            ->method('render')
            ->with($stubDataObject, $stubContextSource)
            ->willReturn($stubSnippetList);

        $mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);
        $mockDataPoolWriter->expects($this->once())
            ->method('writeSnippetList')
            ->with($stubSnippetList);

        $projector = new RootSnippetProjector($mockSnippetRendererCollection, $mockDataPoolWriter);
        $projector->project($stubDataObject, $stubContextSource);
    }
}
