<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\Projector;
use Brera\SnippetList;
use Brera\SnippetRendererCollection;

/**
 * @covers \Brera\Product\ProductSearchAutosuggestionTemplateProjector
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

        $stubSnippetList = $this->getMock(SnippetList::class);

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
        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $projectionSourceDataJson = 'whatever';

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippetList');

        $this->projector->project($projectionSourceDataJson, $stubContextSource);
    }
}
