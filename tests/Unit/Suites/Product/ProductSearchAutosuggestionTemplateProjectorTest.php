<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\SnippetRendererCollection;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionTemplateProjector;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionTemplateProjector
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

        /** @var SnippetRendererCollection|\PHPUnit_Framework_MockObject_MockObject $stubSnippetRendererCollection */
        $stubSnippetRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $stubSnippetRendererCollection->method('render')->willReturn([]);

        $this->projector = new ProductSearchAutosuggestionTemplateProjector(
            $this->mockDataPoolWriter,
            $stubSnippetRendererCollection
        );
    }

    public function testProjectorInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Projector::class, $this->projector);
    }

    public function testSnippetsAreWrittenIntoDataPool()
    {
        $projectionSourceDataJson = 'whatever';

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets');

        $this->projector->project($projectionSourceDataJson);
    }
}
