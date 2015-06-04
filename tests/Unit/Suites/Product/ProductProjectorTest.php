<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\SampleContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use Brera\SnippetList;
use Brera\SnippetRendererCollection;
use Brera\ProjectionSourceData;

/**
 * @covers \Brera\Product\ProductProjector
 * @uses   \Brera\Product\ProductSnippetRendererCollection
 */
class ProductProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductProjector
     */
    private $projector;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetList;

    /**
     * @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocumentCollection;

    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataPoolWriter;

    /**
     * @var SnippetRendererCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRendererCollection;

    /**
     * @var SearchDocumentBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocumentBuilder;

    public function setUp()
    {
        $this->stubSnippetList = $this->getMock(SnippetList::class);
        $this->stubDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);
        $this->stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);

        $this->mockRendererCollection = $this->getMock(ProductSnippetRendererCollection::class, [], [], '', false);
        $this->mockRendererCollection->expects($this->any())
            ->method('render')
            ->willReturn($this->stubSnippetList);

        $this->stubSearchDocumentBuilder = $this->getMock(ProductSearchDocumentBuilder::class, [], [], '', false);

        $this->projector = new ProductProjector(
            $this->mockRendererCollection,
            $this->stubSearchDocumentBuilder,
            $this->stubDataPoolWriter
        );
    }

    /**
     * @test
     */
    public function itShouldSetSnippetListAndSearchDocumentOnDataPoolWriter()
    {
        $this->stubSearchDocumentBuilder->expects($this->once())
            ->method('aggregate')
            ->willReturn($this->stubSearchDocumentCollection);

        $this->stubDataPoolWriter->expects($this->once())
            ->method('writeSnippetList')
            ->with($this->stubSnippetList);

        $this->stubDataPoolWriter->expects($this->once())
            ->method('writeSearchDocumentCollection')
            ->with($this->stubSearchDocumentCollection);

        $stubProduct = $this->getMock(ProductSource::class, [], [], '', false);
        $stubContext = $this->getMock(SampleContextSource::class, [], [], '', false);

        $this->projector->project($stubProduct, $stubContext);
    }

    /**
     * @test
     * @expectedException \Brera\InvalidProjectionDataSourceTypeException
     * @expectedExceptionMessage First argument must be instance of ProductSource.
     */
    public function itShouldThrowIfTheDataSourceTypeIsNotProduct()
    {
        $stubContext = $this->getMock(SampleContextSource::class, [], [], '', false);
        $invalidDataSourceType = $this->getMock(ProjectionSourceData::class);

        $this->projector->project($invalidDataSourceType, $stubContext);
    }
}
