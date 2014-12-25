<?php

namespace Brera\PoC\Product;

use Brera\PoC\SnippetRenderer;
use Brera\PoC\SnippetResultList;
use Brera\PoC\ProjectionSourceData;
use Brera\PoC\Environment;

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

        $this->mockRenderer = $this->getMock(SnippetRenderer::class,
            array('render'));

        $this->mockRenderer2 = $this->getMock(SnippetRenderer::class,
            array('render'));

        $this->rendererCollection = new HardcodedProductSnippetRendererCollection(
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

        $this->rendererCollection->render($stubProduct, $stubEnvironment);
    }

    /**
     * @test
     * @expectedException \Brera\Poc\InvalidProjectionDataSourceType
     */
    public function itShouldThrowAnExceptionIfTheDataSourceObjectTypeIsNotProduct()
    {
        $invalidDataSource = $this->getMock(ProjectionSourceData::class);
        $stubEnvironment = $this->getMock(Environment::class);
        $this->rendererCollection->render($invalidDataSource, $stubEnvironment);
    }
}
