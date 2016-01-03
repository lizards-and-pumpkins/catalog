<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import\Listing;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Snippet;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\Listing\ProductListingPageSnippetProjector
 * @uses   \LizardsAndPumpkins\DataVersion
 */
class ProductListingPageSnippetProjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingPageSnippetProjector
     */
    private $listingProjection;

    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContextSource;

    /**
     * @var ProductListingPageSnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingPageSnippetRenderer;

    /**
     * @var Snippet|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippet;

    protected function setUp()
    {
        $this->stubSnippet = $this->getMock(Snippet::class, [], [], '', false);
        $this->stubProductListingPageSnippetRenderer = $this->getMockBuilder(ProductListingPageSnippetRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();
        $this->stubProductListingPageSnippetRenderer->method('render')->willReturn([$this->stubSnippet]);
        
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);
        
        $this->mockContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->mockContextSource->method('getAllAvailableContextsWithVersion')->willReturn(
            [$this->getMock(Context::class)]
        );
        $this->listingProjection = new ProductListingPageSnippetProjector(
            $this->stubProductListingPageSnippetRenderer,
            $this->mockDataPoolWriter,
            $this->mockContextSource
        );
    }

    public function testSnippetsAreWrittenToKeyValueStore()
    {
        $testVersion = DataVersion::fromVersionString('abc123');
        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets')->with($this->stubSnippet);
        $this->listingProjection->project($testVersion);
    }
}
