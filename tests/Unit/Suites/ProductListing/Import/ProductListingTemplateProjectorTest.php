<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\SnippetRendererCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateProjector
 */
class ProductListingTemplateProjectorTest extends TestCase
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
        /** @var SnippetRendererCollection|\PHPUnit_Framework_MockObject_MockObject $stubSnippetRendererCollection */
        $stubSnippetRendererCollection = $this->createMock(SnippetRendererCollection::class);
        $stubSnippetRendererCollection->method('render')->willReturn([]);

        $this->mockDataPoolWriter = $this->createMock(DataPoolWriter::class);

        $this->projector = new ProductListingTemplateProjector(
            $stubSnippetRendererCollection,
            $this->mockDataPoolWriter
        );
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
