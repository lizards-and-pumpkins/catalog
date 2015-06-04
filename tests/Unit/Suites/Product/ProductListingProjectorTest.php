<?php

namespace Brera\Product;

use Brera\DataPool\DataPoolWriter;
use Brera\ProjectionSourceData;
use Brera\SampleContextSource;
use Brera\Snippet;

/**
 * @covers \Brera\Product\ProductListingProjector
 */
class ProductListingProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingCriteriaSnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductListingPageMetaInfoSnippetRenderer;

    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var ProductListingProjector
     */
    private $projector;

    protected function setUp()
    {
        $this->mockProductListingPageMetaInfoSnippetRenderer = $this->getMock(
            ProductListingCriteriaSnippetRenderer::class,
            [],
            [],
            '',
            false
        );

        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);

        $this->projector = new ProductListingProjector(
            $this->mockProductListingPageMetaInfoSnippetRenderer,
            $this->mockDataPoolWriter
        );
    }

    /**
     * @test
     */
    public function itShouldSetProductListingMetaSnippetOnDataPoolWriter()
    {
        $stubProductListingSource = $this->getMock(ProductListingSource::class, [], [], '', false);
        $stubContext = $this->getMock(SampleContextSource::class, [], [], '', false);
        $stubSnippet = $this->getMock(Snippet::class, [], [], '', false);

        $this->mockProductListingPageMetaInfoSnippetRenderer->expects($this->once())
            ->method('render')
            ->willReturn($stubSnippet);

        $this->mockDataPoolWriter->expects($this->once())
            ->method('writeSnippet')
            ->with($stubSnippet);

        $this->projector->project($stubProductListingSource, $stubContext);
    }

    /**
     * @test
     * @expectedException \Brera\InvalidProjectionDataSourceTypeException
     * @expectedExceptionMessage First argument must be instance of ProductListingSource.
     */
    public function itShouldThrowIfTheDataSourceTypeIsNotProduct()
    {
        $stubContext = $this->getMock(SampleContextSource::class, [], [], '', false);
        $invalidDataSourceType = $this->getMock(ProjectionSourceData::class);

        $this->projector->project($invalidDataSourceType, $stubContext);
    }
}
