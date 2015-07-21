<?php

namespace Brera\Content;

use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\ProjectionSourceData;
use Brera\Projector;
use Brera\SampleContextSource;
use Brera\SnippetList;
use Brera\SnippetRendererCollection;

/**
 * @covers \Brera\Content\ContentBlockProjector
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
        $this->setExpectedException(InvalidProjectionDataSourceTypeException::class);

        $stubProjectionSourceData = $this->getMock(ProjectionSourceData::class);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $this->projector->project($stubProjectionSourceData, $stubContextSource);
    }

    public function testSnippetListIsWrittenIntoDataPool()
    {
        $stubSnippetList = $this->getMock(SnippetList::class);

        $this->mockSnippetRendererCollection->method('render')
            ->willReturn($stubSnippetList);

        $this->mockDataPoolWriter->expects($this->once())
            ->method('writeSnippetList')
            ->with($stubSnippetList);

        $stubContentBlockSource = $this->getMock(ContentBlockSource::class, [], [], '', false);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $this->projector->project($stubContentBlockSource, $stubContextSource);
    }
}
