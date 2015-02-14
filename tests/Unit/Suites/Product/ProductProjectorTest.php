<?php

namespace Brera\Product;

use Brera\Environment\EnvironmentSource;
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
    private $stubProductSnippetRendererCollection;

    /**
     * @var SearchDocumentBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocumentBuilder;

    public function setUp()
    {
        $this->stubSnippetResultList = $this->getMock(SnippetResultList::class);
        $this->stubDataPoolWriter = $this->getMockBuilder(DataPoolWriter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stubSearchDocumentCollection = $this->getMockBuilder(SearchDocumentCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stubProductSnippetRendererCollection = $this->getMockBuilder(ProductSnippetRendererCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubProductSnippetRendererCollection->expects($this->any())
            ->method('render')
            ->willReturn($this->stubSnippetResultList);

        $this->stubSearchDocumentBuilder = $this->getMockBuilder(ProductSearchDocumentBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->projector = new ProductProjector(
            $this->stubProductSnippetRendererCollection,
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

        $stubProduct = $this->getMockBuilder(ProductSource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stubEnvironment = $this->getMockBuilder(EnvironmentSource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->projector->project($stubProduct, $stubEnvironment);
    }

    /**
     * @test
     * @expectedException \Brera\InvalidProjectionDataSourceType
     */
    public function itShouldThrowIfTheDataSourceTypeIsNotProduct()
    {
        $stubEnvironment = $this->getMockBuilder(EnvironmentSource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $invalidDataSourceType = $this->getMock(ProjectionSourceData::class);

        $this->projector->project($invalidDataSourceType, $stubEnvironment);
    }
}
