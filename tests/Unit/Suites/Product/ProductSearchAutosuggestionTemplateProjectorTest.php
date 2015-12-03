<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Projector;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRendererCollection;

/**
 * @covers \LizardsAndPumpkins\Product\ProductSearchAutosuggestionTemplateProjector
 */
class ProductSearchAutosuggestionTemplateProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var ProductSearchAutosuggestionTemplateProjector
     */
    private $projector;

    protected function setUp()
    {
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);

        $stubSnippetList = $this->getMock(SnippetList::class, [], [], '', false);

        /** @var SnippetRendererCollection|\PHPUnit_Framework_MockObject_MockObject $stubSnippetRendererCollection */
        $stubSnippetRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $stubSnippetRendererCollection->method('render')->willReturn($stubSnippetList);

        $this->projector = new ProductSearchAutosuggestionTemplateProjector(
            $this->mockDataPoolWriter,
            $stubSnippetRendererCollection
        );
    }

    public function testProjectorInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Projector::class, $this->projector);
    }

    public function testSnippetListIsWrittenIntoDataPool()
    {
        $projectionSourceDataJson = 'whatever';

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippetList');

        $this->projector->project($projectionSourceDataJson);
    }
}
