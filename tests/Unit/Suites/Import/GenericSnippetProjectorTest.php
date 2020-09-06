<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\GenericSnippetProjector
 */
class GenericSnippetProjectorTest extends TestCase
{
    /**
     * @var DataPoolWriter|MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @param mixed $projectionSourceData
     * @param Snippet $snippet
     * @return SnippetRenderer|MockObject
     */
    private function createStubSnippetRenderer($projectionSourceData, Snippet $snippet): SnippetRenderer
    {
        $stubSnippetRenderer = $this->createMock(SnippetRenderer::class);
        $stubSnippetRenderer->method('render')->with($projectionSourceData)->willReturn([$snippet]);

        return $stubSnippetRenderer;
    }

    final protected function setUp(): void
    {
        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);
    }

    public function testImplementsProjectorInterface(): void
    {
        $this->assertInstanceOf(Projector::class, new GenericSnippetProjector($this->mockDataPoolWriter));
    }

    public function testSnippetIsWrittenIntoDataPool(): void
    {
        $testProjectionSourceData = 'foo';

        $stubSnippetA = $this->createMock(Snippet::class);
        $stubSnippetRendererA = $this->createStubSnippetRenderer($testProjectionSourceData, $stubSnippetA);

        $stubSnippetB = $this->createMock(Snippet::class);
        $stubSnippetRendererB = $this->createStubSnippetRenderer($testProjectionSourceData, $stubSnippetB);

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets')->with($stubSnippetA, $stubSnippetB);

        $projector = new GenericSnippetProjector($this->mockDataPoolWriter, $stubSnippetRendererA, $stubSnippetRendererB);
        $projector->project($testProjectionSourceData);
    }
}
