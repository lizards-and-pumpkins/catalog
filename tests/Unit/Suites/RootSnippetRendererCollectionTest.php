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
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetResultList;

    /**
     * @var RootSnippetRendererCollection
     */
    private $rootSnippetRendererCollection;

    protected function setUp()
    {
        $this->mockSnippetRenderer = $this->getMock(SnippetRenderer::class);

        $this->mockSnippetResultList = $this->getMock(SnippetResultList::class);

        $this->rootSnippetRendererCollection = new RootSnippetRendererCollection(
            [$this->mockSnippetRenderer],
            $this->mockSnippetResultList
        );
    }

    /**
     * @test
     */
    public function itShouldReturnSnippetResultsList()
    {
        $stubRootSnippetSourceList = $this->getMock(RootSnippetSourceList::class, [], [], '', false);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $stubSnippetResultList = $this->getMock(SnippetResultList::class);

        $this->mockSnippetRenderer->expects($this->once())
            ->method('render')
            ->with($stubRootSnippetSourceList, $stubContextSource)
            ->willReturn($stubSnippetResultList);

        $this->mockSnippetResultList->expects($this->once())
            ->method('merge')
            ->with($stubSnippetResultList)
            ->willReturn($stubSnippetResultList);

        $result = $this->rootSnippetRendererCollection->render($stubRootSnippetSourceList, $stubContextSource);

        $this->assertEquals($stubSnippetResultList, $result);
    }

    /**
     * @test
     * @expectedException \Brera\InvalidProjectionDataSourceTypeException
     */
    public function itShouldThrowAnExceptionIfProjectionDataIsNotAnInstanceOfRootSnippetSourceList()
    {
        $stubProjectionSourceData = $this->getMock(ProjectionSourceData::class);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $this->rootSnippetRendererCollection->render($stubProjectionSourceData, $stubContextSource);
    }
}
