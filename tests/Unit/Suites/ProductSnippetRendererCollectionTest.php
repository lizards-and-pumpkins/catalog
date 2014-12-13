<?php

namespace Brera\PoC;

use Brera\PoC\Product\Product;

class ProductSnippetRendererCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductSnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRenderer;

    /**
     * @var ProductSnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRenderer2;

    /**
     * @var HardcodedProductSnippetRendererCollection
     */
    private $rendererCollection;

    /**
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetResultList;


    public function setUp()
    {
        $this->stubSnippetResultList
            = $this->getMock(SnippetResultList::class, array('merge'));

        $this->mockRenderer = $this->getMock(ProductSnippetRenderer::class,
            array('render'));

        $this->mockRenderer2 = $this->getMock(ProductSnippetRenderer::class,
            array('render'));

        $rendererArray = [$this->mockRenderer, $this->mockRenderer2];

        $this->rendererCollection
            = new HardcodedProductSnippetRendererCollection(
            $rendererArray,
            $this->stubSnippetResultList
        );
    }

    /**
     * @test
     */
    public function itShouldReturnARenderResultList()
    {
        $stubProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stubEnvironment = $this->getMock(Environment::class);


        $snippetResultList = $this->rendererCollection->render(
            $stubProduct, $stubEnvironment
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
        $stubProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stubEnvironment = $this->getMock(Environment::class);

        $stubSnippetResultListFromRenderer
            = $this->getMock(SnippetResultList::class);

        $this->mockRenderer->expects($this->once())->method('render')
            ->with($stubProduct, $stubEnvironment)
            ->willReturn($stubSnippetResultListFromRenderer);

        $this->mockRenderer2->expects($this->once())->method('render')
            ->with($stubProduct, $stubEnvironment)
            ->willReturn($stubSnippetResultListFromRenderer);

        $this->rendererCollection->render(
            $stubProduct, $stubEnvironment
        );
    }

    /**
     * @test
     */
    public function itShouldMergeTheRestultsOfTheRenderers()
    {
        $stubProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stubEnvironment = $this->getMock(Environment::class);

        $stubSnippetResultListFromRenderer
            = $this->getMock(SnippetResultList::class);

        $stubSnippetResultListFromRenderer2
            = $this->getMock(SnippetResultList::class);

        $this->mockRenderer->expects($this->any())->method('render')
            ->with($stubProduct, $stubEnvironment)
            ->willReturn($stubSnippetResultListFromRenderer);

        $this->mockRenderer2->expects($this->any())->method('render')
            ->with($stubProduct, $stubEnvironment)
            ->willReturn($stubSnippetResultListFromRenderer2);

        $this->stubSnippetResultList->expects($this->exactly(2))
            ->method('merge')
            ->withConsecutive(
                [$this->identicalTo($stubSnippetResultListFromRenderer)],
                [$this->identicalTo($stubSnippetResultListFromRenderer2)]
            );

        $this->rendererCollection->render(
            $stubProduct, $stubEnvironment
        );
    }
}
