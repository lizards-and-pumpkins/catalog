<?php

namespace Brera;

/**
 * @covers \Brera\RootSnippetRendererCollection
 */
class RootSnippetRendererCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetRenderer;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var RootSnippetRendererCollection
     */
    private $rootSnippetRendererCollection;

    protected function setUp()
    {
        $this->mockSnippetRenderer = $this->getMockBuilder(SnippetRenderer::class)
            ->setMethods(['render'])
            ->getMock();

        $this->mockSnippetList = $this->getMock(SnippetList::class);

        $this->rootSnippetRendererCollection = new RootSnippetRendererCollection(
            [$this->mockSnippetRenderer],
            $this->mockSnippetList
        );
    }

    public function testSnippetListIsReturned()
    {
        $stubRootSnippetSourceList = $this->getMock(RootSnippetSourceList::class, [], [], '', false);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $stubSnippetList = $this->getMock(SnippetList::class);

        $this->mockSnippetRenderer->expects($this->once())
            ->method('render')
            ->with($stubRootSnippetSourceList, $stubContextSource)
            ->willReturn($stubSnippetList);

        $this->mockSnippetList->expects($this->once())
            ->method('merge')
            ->with($stubSnippetList)
            ->willReturn($stubSnippetList);

        $result = $this->rootSnippetRendererCollection->render($stubRootSnippetSourceList, $stubContextSource);

        $this->assertEquals($stubSnippetList, $result);
    }

    public function testExceptionIsThrownIfProjectionDataIsNotInstanceOfRootSnippetSourceList()
    {
        $stubProjectionSourceData = $this->getMock(ProjectionSourceData::class);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $this->setExpectedException(InvalidProjectionDataSourceTypeException::class);

        $this->rootSnippetRendererCollection->render($stubProjectionSourceData, $stubContextSource);
    }
}
