<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\ContextSource;

/**
 * @covers \LizardsAndPumpkins\SnippetRendererCollection
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
        $this->mockRenderer->method('render')->willReturn($this->getMock(SnippetList::class));
        $this->mockRenderer2->method('render')->willReturn($this->getMock(SnippetList::class));

        $snippetList = $this->rendererCollection->render('test-projection-source-data');

        $this->assertInstanceOf(SnippetList::class, $snippetList);
        $this->assertSame($this->stubSnippetList, $snippetList);
    }

    public function testRenderingIsDelegatedToSnippetRenderers()
    {
        $testProjectionSourceData = 'test-projection-source-data';
        $stubSnippetListFromRenderer = $this->getMock(SnippetList::class);

        $this->mockRenderer->expects($this->once())
            ->method('render')
            ->willReturn($stubSnippetListFromRenderer);

        $this->mockRenderer2->expects($this->once())
            ->method('render')
            ->willReturn($stubSnippetListFromRenderer);

        $this->rendererCollection->render($testProjectionSourceData);
    }

    public function testResultsOfRenderersAreMerged()
    {
        $testProjectionSourceData = 'test-projection-source-data';

        $stubSnippetListFromRenderer = $this->getMock(SnippetList::class);
        $stubSnippetListFromRenderer2 = $this->getMock(SnippetList::class);

        $this->mockRenderer->method('render')
            ->willReturn($stubSnippetListFromRenderer);

        $this->mockRenderer2->method('render')
            ->willReturn($stubSnippetListFromRenderer2);

        $this->stubSnippetList->expects($this->exactly(2))
            ->method('merge')
            ->withConsecutive(
                [$this->identicalTo($stubSnippetListFromRenderer)],
                [$this->identicalTo($stubSnippetListFromRenderer2)]
            );

        $this->rendererCollection->render($testProjectionSourceData);
    }

    /**
     * @return ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubContextSource()
    {
        return $this->getMock(ContextSource::class, [], [], '', false);
    }
}
