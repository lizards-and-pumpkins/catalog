<?php

namespace Brera;

/**
 * @covers \Brera\SnippetRendererCollection
 */
class SnippetRendererCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRenderer;

    /**
     * @var SnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRenderer2;

    /**
     * @var SnippetRendererCollection
     */
    private $rendererCollection;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetList;

    public function setUp()
    {
        $this->stubSnippetList = $this->getMockBuilder(SnippetList::class)
            ->setMethods(['merge'])
            ->getMock();
        $this->mockRenderer = $this->getMockBuilder(SnippetRenderer::class)
            ->setMethods(['render'])
            ->getMock();
        $this->mockRenderer2 = $this->getMockBuilder(SnippetRenderer::class)
            ->setMethods(['render'])
            ->getMock();

        $this->rendererCollection = new SnippetRendererCollection(
            [$this->mockRenderer, $this->mockRenderer2],
            $this->stubSnippetList
        );
    }

    public function testRenderedSnippetListIsReturned()
    {
        $this->mockRenderer->method('render')
            ->willReturn($this->getMock(SnippetList::class));

        $this->mockRenderer2->method('render')
            ->willReturn($this->getMock(SnippetList::class));

        $stubProjectionSourceData = $this->getMock(ProjectionSourceData::class);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $snippetList = $this->rendererCollection->render($stubProjectionSourceData, $stubContextSource);

        $this->assertInstanceOf(SnippetList::class, $snippetList);
        $this->assertSame($this->stubSnippetList, $snippetList);
    }

    public function testRenderingIsDelegatedToSnippetRenderers()
    {
        $stubProjectionSourceData = $this->getMock(ProjectionSourceData::class);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $stubSnippetListFromRenderer = $this->getMock(SnippetList::class);

        $this->mockRenderer->expects($this->once())
            ->method('render')
            ->with($stubProjectionSourceData, $stubContextSource)
            ->willReturn($stubSnippetListFromRenderer);

        $this->mockRenderer2->expects($this->once())
            ->method('render')
            ->with($stubProjectionSourceData, $stubContextSource)
            ->willReturn($stubSnippetListFromRenderer);

        $this->rendererCollection->render($stubProjectionSourceData, $stubContextSource);
    }

    public function testResultsOfRenderersAreMerged()
    {
        $stubProjectionSourceData = $this->getMock(ProjectionSourceData::class);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $stubSnippetListFromRenderer = $this->getMock(SnippetList::class);
        $stubSnippetListFromRenderer2 = $this->getMock(SnippetList::class);

        $this->mockRenderer->method('render')
            ->with($stubProjectionSourceData, $stubContextSource)
            ->willReturn($stubSnippetListFromRenderer);

        $this->mockRenderer2->method('render')
            ->with($stubProjectionSourceData, $stubContextSource)
            ->willReturn($stubSnippetListFromRenderer2);

        $this->stubSnippetList->expects($this->exactly(2))
            ->method('merge')
            ->withConsecutive(
                [$this->identicalTo($stubSnippetListFromRenderer)],
                [$this->identicalTo($stubSnippetListFromRenderer2)]
            );

        $this->rendererCollection->render($stubProjectionSourceData, $stubContextSource);
    }
}
