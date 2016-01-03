<?php

namespace LizardsAndPumpkins;

/**
 * @covers \LizardsAndPumpkins\SnippetRendererCollection
 * @uses   \LizardsAndPumpkins\SnippetList
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
     * @param Snippet[] ...$snippets
     * @return SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSnippetList(Snippet ...$snippets)
    {
        $stubSnippetList = $this->getMock(SnippetList::class);
        $stubSnippetList->method('getIterator')->willReturn(new \ArrayIterator($snippets));

        return $stubSnippetList;
    }

    public function setUp()
    {
        $this->mockRenderer = $this->getMockBuilder(SnippetRenderer::class)->setMethods(['render'])->getMock();
        $this->mockRenderer2 = $this->getMockBuilder(SnippetRenderer::class)->setMethods(['render'])->getMock();

        $this->rendererCollection = new SnippetRendererCollection([$this->mockRenderer, $this->mockRenderer2]);
    }

    public function testRenderedSnippetListIsReturned()
    {
        $stubSnippetList = $this->createStubSnippetList();
        $this->mockRenderer->method('render')->willReturn($stubSnippetList);
        $this->mockRenderer2->method('render')->willReturn($stubSnippetList);

        $result = $this->rendererCollection->render('test-projection-source-data');

        $this->assertInstanceOf(SnippetList::class, $result);
    }

    public function testRenderingIsDelegatedToSnippetRenderers()
    {
        $testProjectionSourceData = 'test-projection-source-data';
        $stubSnippetListFromRenderer = $this->createStubSnippetList();

        $this->mockRenderer->expects($this->once())->method('render')->willReturn($stubSnippetListFromRenderer);
        $this->mockRenderer2->expects($this->once())->method('render')->willReturn($stubSnippetListFromRenderer);

        $this->rendererCollection->render($testProjectionSourceData);
    }

    public function testResultsOfRenderersAreMerged()
    {
        $stubSnippet = $this->getMock(Snippet::class, [], [], '', false);

        $testProjectionSourceData = 'test-projection-source-data';

        $stubSnippetList = $this->createStubSnippetList($stubSnippet);
        $stubSnippetList2 = $this->createStubSnippetList($stubSnippet);

        $this->mockRenderer->method('render')->willReturn($stubSnippetList);
        $this->mockRenderer2->method('render')->willReturn($stubSnippetList2);

        $result = $this->rendererCollection->render($testProjectionSourceData);

        $this->assertEquals([$stubSnippet, $stubSnippet], iterator_to_array($result));
    }
}
