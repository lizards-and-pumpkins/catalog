<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;

/**
 * @covers \LizardsAndPumpkins\Import\SnippetRendererCollection
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

    public function setUp()
    {
        $this->mockRenderer = $this->getMockBuilder(SnippetRenderer::class)->setMethods(['render'])->getMock();
        $this->mockRenderer2 = $this->getMockBuilder(SnippetRenderer::class)->setMethods(['render'])->getMock();

        $this->rendererCollection = new SnippetRendererCollection([$this->mockRenderer, $this->mockRenderer2]);
    }

    public function testArrayOfSnippetsIsReturned()
    {
        $stubSnippet = $this->createMock(Snippet::class);

        $this->mockRenderer->method('render')->willReturn([$stubSnippet]);
        $this->mockRenderer2->method('render')->willReturn([$stubSnippet]);
        $result = $this->rendererCollection->render('test-projection-source-data');

        $this->assertContainsOnly(Snippet::class, $result);
    }

    public function testRenderingIsDelegatedToSnippetRenderers()
    {
        $testProjectionSourceData = 'test-projection-source-data';

        $this->mockRenderer->expects($this->once())->method('render')->willReturn([]);
        $this->mockRenderer2->expects($this->once())->method('render')->willReturn([]);

        $this->rendererCollection->render($testProjectionSourceData);
    }

    public function testResultsOfRenderersAreMerged()
    {
        $stubSnippet = $this->createMock(Snippet::class);

        $testProjectionSourceData = 'test-projection-source-data';

        $this->mockRenderer->method('render')->willReturn([$stubSnippet]);
        $this->mockRenderer2->method('render')->willReturn([$stubSnippet]);

        $result = $this->rendererCollection->render($testProjectionSourceData);

        $this->assertEquals([$stubSnippet, $stubSnippet], $result);
    }
}
