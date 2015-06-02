<?php

namespace Brera\Product;

use Brera\ProjectionSourceData;
use Brera\SampleContextSource;
use Brera\SnippetRenderer;
use Brera\SnippetList;

/**
 * @covers \Brera\Product\ProductSnippetRendererCollection
 */
class ProductSnippetRendererCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRenderer;

    /**
     * @var SnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRenderer2;

    /**
     * @var ProductSnippetRendererCollection
     */
    private $rendererCollection;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetList;

    public function setUp()
    {
        $this->stubSnippetList = $this->getMockBuilder(SnippetList::class)
            ->setMethods(['merge'])
            ->getMock();
        $this->mockRenderer = $this->getMockBuilder(SnippetRenderer::class)
            ->setMethods(['render'])
            ->getMock();
        $this->mockRenderer2 = $this->getMockBuilder(SnippetRenderer::class)
            ->setMethods(['render'])
            ->getMock();

        $this->rendererCollection = new ProductSnippetRendererCollection(
            [$this->mockRenderer, $this->mockRenderer2],
            $this->stubSnippetList
        );
    }

    /**
     * @test
     */
    public function itShouldReturnARenderedSnippetList()
    {
        $this->mockRenderer->expects($this->any())->method('render')
            ->willReturn($this->getMock(SnippetList::class));

        $this->mockRenderer2->expects($this->any())->method('render')
            ->willReturn($this->getMock(SnippetList::class));
        /* @var $stubProduct \PHPUnit_Framework_MockObject_MockObject|Product */
        $stubProduct = $this->getMock(ProductSource::class, [], [], '', false);
        /* @var $stubContextSource \PHPUnit_Framework_MockObject_MockObject|SampleContextSource */
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $snippetList = $this->rendererCollection->render(
            $stubProduct,
            $stubContextSource
        );
        $this->assertInstanceOf(
            SnippetList::class,
            $snippetList
        );

        $this->assertSame($this->stubSnippetList, $snippetList);
    }

    /**
     * @test
     */
    public function itShouldDelegateRenderingToSnippetRenderers()
    {
        /* @var $stubProduct \PHPUnit_Framework_MockObject_MockObject|Product */
        $stubProduct = $this->getMock(ProductSource::class, [], [], '', false);
        /* @var $stubContextSource \PHPUnit_Framework_MockObject_MockObject|SampleContextSource */
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $stubSnippetListFromRenderer
            = $this->getMock(SnippetList::class);

        $this->mockRenderer->expects($this->once())->method('render')
            ->with($stubProduct, $stubContextSource)
            ->willReturn($stubSnippetListFromRenderer);

        $this->mockRenderer2->expects($this->once())->method('render')
            ->with($stubProduct, $stubContextSource)
            ->willReturn($stubSnippetListFromRenderer);

        $this->rendererCollection->render(
            $stubProduct,
            $stubContextSource
        );
    }

    /**
     * @test
     */
    public function itShouldMergeTheResultsOfTheRenderers()
    {
        /* @var $stubProduct \PHPUnit_Framework_MockObject_MockObject|Product */
        $stubProduct = $this->getMock(ProductSource::class, [], [], '', false);
        /* @var $stubContextSource \PHPUnit_Framework_MockObject_MockObject|SampleContextSource */
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $stubSnippetListFromRenderer = $this->getMock(SnippetList::class);
        $stubSnippetListFromRenderer2 = $this->getMock(SnippetList::class);

        $this->mockRenderer->expects($this->any())->method('render')
            ->with($stubProduct, $stubContextSource)
            ->willReturn($stubSnippetListFromRenderer);

        $this->mockRenderer2->expects($this->any())->method('render')
            ->with($stubProduct, $stubContextSource)
            ->willReturn($stubSnippetListFromRenderer2);

        $this->stubSnippetList->expects($this->exactly(2))
            ->method('merge')
            ->withConsecutive(
                [$this->identicalTo($stubSnippetListFromRenderer)],
                [$this->identicalTo($stubSnippetListFromRenderer2)]
            );

        $this->rendererCollection->render($stubProduct, $stubContextSource);
    }

    /**
     * @test
     * @expectedException \Brera\InvalidProjectionDataSourceTypeException
     */
    public function itShouldThrowAnExceptionIfTheDataSourceObjectTypeIsNotProduct()
    {
        /* @var $invalidDataSource \PHPUnit_Framework_MockObject_MockObject|ProjectionSourceData */
        $invalidDataSource = $this->getMock(ProjectionSourceData::class);
        /* @var $stubContextSource \PHPUnit_Framework_MockObject_MockObject|SampleContextSource */
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $this->rendererCollection->render($invalidDataSource, $stubContextSource);
    }
}
