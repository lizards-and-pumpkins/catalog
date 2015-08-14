<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\RootSnippetSourceList;
use Brera\RootSnippetSourceListBuilder;
use Brera\SnippetList;
use Brera\SnippetRendererCollection;

/**
 * @covers \Brera\Product\ProductListingTemplateProjector
 */
class ProductListingTemplateProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var ProductListingTemplateProjector
     */
    private $projector;

    protected function setUp()
    {
        $stubRootSnippetSourceList = $this->getMock(RootSnippetSourceList::class, [], [], '', false);

        /**
         * @var RootSnippetSourceListBuilder|\PHPUnit_Framework_MockObject_MockObject $stubRootSnippetSourceListBuilder
         */
        $stubRootSnippetSourceListBuilder = $this->getMock(RootSnippetSourceListBuilder::class, [], [], '', false);
        $stubRootSnippetSourceListBuilder->method('fromJson')->willReturn($stubRootSnippetSourceList);

        $stubSnippetList = $this->getMock(SnippetList::class);

        /** @var SnippetRendererCollection|\PHPUnit_Framework_MockObject_MockObject $stubSnippetRendererCollection */
        $stubSnippetRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $stubSnippetRendererCollection->method('render')->willReturn($stubSnippetList);

        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);

        $this->projector = new ProductListingTemplateProjector(
            $stubSnippetRendererCollection,
            $this->mockDataPoolWriter,
            $stubRootSnippetSourceListBuilder
        );
    }

    public function testSnippetListIsWrittenIntoDataPool()
    {
        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $projectionSourceDataJson = '{}';

        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippetList');

        $this->projector->project($projectionSourceDataJson, $stubContextSource);
    }
}
