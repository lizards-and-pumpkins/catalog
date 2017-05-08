<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\GenericProjector
 */
class GenericProjectorTest extends TestCase
{
    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    final protected function setUp()
    {
        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);
    }

    public function testImplementsProjectorInterface()
    {
        $this->assertInstanceOf(Projector::class, new GenericProjector($this->mockDataPoolWriter));
    }

    public function testSnippetIsWrittenIntoDataPool()
    {
        $testProjectionSourceData = 'foo';

        $stubSnippetA = $this->createMock(Snippet::class);
        $stubSnippetRendererA = $this->getMockBuilder(SnippetRenderer::class)
            ->setMethods(['render'])
            ->getMock();
        $stubSnippetRendererA->method('render')->with($testProjectionSourceData)->willReturn($stubSnippetA);

        $stubSnippetB = $this->createMock(Snippet::class);
        $stubSnippetRendererB = $this->getMockBuilder(SnippetRenderer::class)
            ->setMethods(['render'])
            ->getMock();
        $stubSnippetRendererB->method('render')->with($testProjectionSourceData)->willReturn($stubSnippetB);

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets')->with($stubSnippetA, $stubSnippetB);

        $projector = new GenericProjector($this->mockDataPoolWriter, $stubSnippetRendererA, $stubSnippetRendererB);
        $projector->project($testProjectionSourceData);
    }
}
