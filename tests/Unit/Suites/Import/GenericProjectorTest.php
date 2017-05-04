<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
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

    /**
     * @var GenericProjector
     */
    private $projector;

    protected function setUp()
    {
        /** @var SnippetRendererCollection|\PHPUnit_Framework_MockObject_MockObject $stubSnippetRendererCollection */
        $stubSnippetRendererCollection = $this->createMock(SnippetRendererCollection::class);
        $stubSnippetRendererCollection->method('render')->willReturn([]);

        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);

        $this->projector = new GenericProjector($stubSnippetRendererCollection, $this->mockDataPoolWriter);
    }

    public function testProjectorInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Projector::class, $this->projector);
    }

    public function testSnippetIsWrittenIntoDataPool()
    {
        $projectionSourceDataJson = '{}';

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets');

        $this->projector->project($projectionSourceDataJson);
    }
}
