<?php

namespace Brera;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;

/**
 * @covers \Brera\RootSnippetProjector
 */
class RootSnippetProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldWriteSnippetResultCollectionIntoDataPool()
    {
        $stubDataObject = $this->getMock(ProjectionSourceData::class);
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $stubSnippetResultsList = $this->getMock(SnippetResultList::class);

        $mockSnippetRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $mockSnippetRendererCollection->expects($this->once())
            ->method('render')
            ->with($stubDataObject, $stubContextSource)
            ->willReturn($stubSnippetResultsList);

        $mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);
        $mockDataPoolWriter->expects($this->once())
            ->method('writeSnippetResultList')
            ->with($stubSnippetResultsList);

        $projector = new RootSnippetProjector($mockSnippetRendererCollection, $mockDataPoolWriter);
        $projector->project($stubDataObject, $stubContextSource);
    }
}
