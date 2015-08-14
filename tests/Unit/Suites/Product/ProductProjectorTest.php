<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\InvalidProjectionSourceDataTypeException;
use Brera\SampleContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\SnippetList;
use Brera\SnippetRendererCollection;

/**
 * @covers \Brera\Product\ProductProjector
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
    private $mockDataPoolWriter;

    /**
     * @var SnippetRendererCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRendererCollection;

    /**
     * @var ProductSearchDocumentBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocumentBuilder;

    public function setUp()
    {
        $this->stubSnippetList = $this->getMock(SnippetList::class);
        $this->mockDataPoolWriter = $this->getMock(DataPoolWriter::class, [], [], '', false);
        $this->stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);

        $this->mockRendererCollection = $this->getMock(SnippetRendererCollection::class, [], [], '', false);
        $this->mockRendererCollection->method('render')->willReturn($this->stubSnippetList);

        $this->stubSearchDocumentBuilder = $this->getMock(ProductSearchDocumentBuilder::class, [], [], '', false);

        $this->projector = new ProductProjector(
            $this->mockRendererCollection,
            $this->stubSearchDocumentBuilder,
            $this->mockDataPoolWriter
        );
    }

    public function testSnippetListAndSearchDocumentAreSetOnDataPoolWriter()
    {
        $this->stubSearchDocumentBuilder->expects($this->once())
            ->method('aggregate')
            ->willReturn($this->stubSearchDocumentCollection);

        $this->mockDataPoolWriter->expects($this->once())
            ->method('writeSnippetList')
            ->with($this->stubSnippetList);

        $this->mockDataPoolWriter->expects($this->once())
            ->method('writeSearchDocumentCollection')
            ->with($this->stubSearchDocumentCollection);

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $stubProduct = $this->getMock(ProductSource::class, [], [], '', false);

        $this->projector->project($stubProduct, $stubContextSource);
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotProduct()
    {
        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);

        $this->projector->project('invalid-projection-source-data', $stubContextSource);
    }
}
