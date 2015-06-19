<?php

namespace Brera\Product;

use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionDataSourceTypeException;
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

    public function testProductListingMetaSnippetIsSetOnDataPoolWriter()
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

    public function testExceptionIsThrownIfTheDataSourceTypeIsNotProduct()
    {
        $stubContext = $this->getMock(SampleContextSource::class, [], [], '', false);
        $invalidDataSourceType = $this->getMock(ProjectionSourceData::class);

        $this->setExpectedException(
            InvalidProjectionDataSourceTypeException::class,
            'First argument must be instance of ProductListingSource.'
        );
        $this->projector->project($invalidDataSourceType, $stubContext);
    }
}
