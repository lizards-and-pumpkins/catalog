<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\SnippetRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockProjector
 */
class ContentBlockProjectorTest extends TestCase
{
    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @param ContentBlockSource|\PHPUnit_Framework_MockObject_MockObject $contentBlockSource
     * @param Snippet|\PHPUnit_Framework_MockObject_MockObject $snippet
     * @return SnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSnippetRenderer(ContentBlockSource $contentBlockSource, Snippet $snippet)
    {
        $stubSnippetRenderer = $this->getMockBuilder(SnippetRenderer::class)->setMethods(['render'])->getMock();
        $stubSnippetRenderer->method('render')->with($contentBlockSource)->willReturn([$snippet]);

        return $stubSnippetRenderer;
    }

    final protected function setUp()
    {
        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);
    }

    public function testImplementsProjectorInterface()
    {
        $this->assertInstanceOf(Projector::class, new ContentBlockProjector($this->mockDataPoolWriter));
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotAnInstanceOfContentBlockSource()
    {
        $this->expectException(\TypeError::class);
        (new ContentBlockProjector($this->mockDataPoolWriter))->project($projectionSourceData = 'foo');
    }

    public function testSnippetIsWrittenIntoDataPool()
    {
        $dummyContentBlockSource = $this->createMock(ContentBlockSource::class);

        $stubSnippetA = $this->createMock(Snippet::class);
        $stubSnippetRendererA = $this->createStubSnippetRenderer($dummyContentBlockSource, $stubSnippetA);

        $stubSnippetB = $this->createMock(Snippet::class);
        $stubSnippetRendererB = $this->createStubSnippetRenderer($dummyContentBlockSource, $stubSnippetA);

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets')->with($stubSnippetA, $stubSnippetB);

        $projector = new ContentBlockProjector($this->mockDataPoolWriter, $stubSnippetRendererA, $stubSnippetRendererB);
        $projector->project($dummyContentBlockSource);
    }
}
