<?php

namespace Brera\Product;

use Brera\ProjectionSourceData;
use Brera\SampleContextSource;
use Brera\SnippetRenderer;
use Brera\SnippetResultList;

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
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetResultList;

    public function setUp()
    {
        $this->stubSnippetResultList = $this->getMockBuilder(SnippetResultList::class)
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
            $this->stubSnippetResultList
        );
    }

    /**
     * @test
     */
    public function itShouldReturnARenderedSnippetResultList()
    {
        $this->mockRenderer->expects($this->any())->method('render')
            ->willReturn($this->getMock(SnippetResultList::class));

        $this->mockRenderer2->expects($this->any())->method('render')
            ->willReturn($this->getMock(SnippetResultList::class));
        /* @var $stubProduct \PHPUnit_Framework_MockObject_MockObject|Product */
        $stubProduct = $this->getMock(ProductSource::class, [], [], '', false);
        /* @var $stubContextSource \PHPUnit_Framework_MockObject_MockObject|SampleContextSource */
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);

        $snippetResultList = $this->rendererCollection->render(
            $stubProduct,
            $stubContextSource
        );
        $this->assertInstanceOf(
            SnippetResultList::class,
            $snippetResultList
        );

        $this->assertSame($this->stubSnippetResultList, $snippetResultList);
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

        $stubSnippetResultListFromRenderer
            = $this->getMock(SnippetResultList::class);

        $this->mockRenderer->expects($this->once())->method('render')
            ->with($stubProduct, $stubContextSource)
            ->willReturn($stubSnippetResultListFromRenderer);

        $this->mockRenderer2->expects($this->once())->method('render')
            ->with($stubProduct, $stubContextSource)
            ->willReturn($stubSnippetResultListFromRenderer);

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

        $stubSnippetResultListFromRenderer
            = $this->getMock(SnippetResultList::class);

        $stubSnippetResultListFromRenderer2
            = $this->getMock(SnippetResultList::class);

        $this->mockRenderer->expects($this->any())->method('render')
            ->with($stubProduct, $stubContextSource)
            ->willReturn($stubSnippetResultListFromRenderer);

        $this->mockRenderer2->expects($this->any())->method('render')
            ->with($stubProduct, $stubContextSource)
            ->willReturn($stubSnippetResultListFromRenderer2);

        $this->stubSnippetResultList->expects($this->exactly(2))
            ->method('merge')
            ->withConsecutive(
                [$this->identicalTo($stubSnippetResultListFromRenderer)],
                [$this->identicalTo($stubSnippetResultListFromRenderer2)]
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
