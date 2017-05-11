<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\GenericSnipetProjector
 */
class GenericSnippetProjectorTest extends TestCase
{
    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @param mixed $projectionSourceData
     * @param Snippet|\PHPUnit_Framework_MockObject_MockObject $snippet
     * @return SnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSnippetRenderer($projectionSourceData, Snippet $snippet)
    {
        $stubSnippetRenderer = $this->getMockBuilder(SnippetRenderer::class)->setMethods(['render'])->getMock();
        $stubSnippetRenderer->method('render')->with($projectionSourceData)->willReturn([$snippet]);

        return $stubSnippetRenderer;
    }

    final protected function setUp()
    {
        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);
    }

    public function testImplementsProjectorInterface()
    {
        $this->assertInstanceOf(Projector::class, new GenericSnipetProjector($this->mockDataPoolWriter));
    }

    public function testSnippetIsWrittenIntoDataPool()
    {
        $testProjectionSourceData = 'foo';

        $stubSnippetA = $this->createMock(Snippet::class);
        $stubSnippetRendererA = $this->createStubSnippetRenderer($testProjectionSourceData, $stubSnippetA);

        $stubSnippetB = $this->createMock(Snippet::class);
        $stubSnippetRendererB = $this->createStubSnippetRenderer($testProjectionSourceData, $stubSnippetB);

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets')->with($stubSnippetA, $stubSnippetB);

        $projector = new GenericSnipetProjector($this->mockDataPoolWriter, $stubSnippetRendererA, $stubSnippetRendererB);
        $projector->project($testProjectionSourceData);
    }
}
