<?php

namespace Brera\Product;

use Brera\SampleContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\DataPool\SearchEngine\SearchDocumentBuilder;
use Brera\DataPool\SearchEngine\SearchDocumentCollection;
use Brera\SnippetResultList;
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
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetResultList;

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
        $this->stubSnippetResultList = $this->getMock(SnippetResultList::class);
        $this->stubDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);
        $this->stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);

        $this->mockRendererCollection = $this->getMock(ProductSnippetRendererCollection::class, [], [], '', false);
        $this->mockRendererCollection->expects($this->any())
            ->method('render')
            ->willReturn($this->stubSnippetResultList);

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
    public function itShouldSetSnippetResultListAndSearchDocumentOnDataPoolWriter()
    {
        $this->stubSearchDocumentBuilder->expects($this->once())
            ->method('aggregate')
            ->willReturn($this->stubSearchDocumentCollection);

        $this->stubDataPoolWriter->expects($this->once())
            ->method('writeSnippetResultList')
            ->with($this->stubSnippetResultList);

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
