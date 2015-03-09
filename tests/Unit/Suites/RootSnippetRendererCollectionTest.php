<?php

namespace Brera;

/**
 * @covers \Brera\RootSnippetRendererCollection
 */
class RootSnippetRendererCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnSnippetResultsList()
    {
        $stubProjectionSourceData = $this->getMock(ProjectionSourceData::class);
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $stubSnippetResultList = $this->getMock(SnippetResultList::class);

        $mockSnippetRenderer = $this->getMock(SnippetRenderer::class);
        $mockSnippetRenderer->expects($this->once())
            ->method('render')
            ->with($stubProjectionSourceData, $stubContextSource)
            ->willReturn($stubSnippetResultList);

        $mockSnippetResultList = $this->getMock(SnippetResultList::class);
        $mockSnippetResultList->expects($this->once())
            ->method('merge')
            ->with($stubSnippetResultList)
            ->willReturn($stubSnippetResultList);

        $rootSnippetRendererCollection = new RootSnippetRendererCollection(
            [$mockSnippetRenderer],
            $mockSnippetResultList
        );

        $result = $rootSnippetRendererCollection->render($stubProjectionSourceData, $stubContextSource);

        $this->assertEquals($stubSnippetResultList, $result);
    }
}
