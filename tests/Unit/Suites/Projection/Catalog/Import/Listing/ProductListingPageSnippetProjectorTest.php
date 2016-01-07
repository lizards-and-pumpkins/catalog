<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import\Listing;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projection\Projector;
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
    private $projector;

    /**
     * @var DataPoolWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolWriter;

    /**
     * @var Snippet|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippet;

    protected function setUp()
    {
        $this->stubSnippet = $this->getMock(Snippet::class, [], [], '', false);

        /** @var ProductListingPageSnippetRenderer|\PHPUnit_Framework_MockObject_MockObject $stubSnippetRenderer */
        $stubSnippetRenderer = $this->getMock(ProductListingPageSnippetRenderer::class, [], [], '', false);
        $stubSnippetRenderer->method('render')->willReturn([$this->stubSnippet]);
        
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $stubContextSource->method('getAllAvailableContextsWithVersion')->willReturn([$this->getMock(Context::class)]);

        $this->projector = new ProductListingPageSnippetProjector(
            $stubSnippetRenderer,
            $this->mockDataPoolWriter,
            $stubContextSource
        );
    }

    public function testProjectorInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Projector::class, $this->projector);
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotAnInstanceOfDataVersion()
    {
        $invalidProjectionSourceData = new \stdClass();
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);
        $this->projector->project($invalidProjectionSourceData);
    }

    public function testSnippetsAreWrittenToKeyValueStore()
    {
        $testVersion = DataVersion::fromVersionString('abc123');
        $this->mockDataPoolWriter->expects($this->once())->method('writeSnippets')->with($this->stubSnippet);
        $this->projector->project($testVersion);
    }
}
