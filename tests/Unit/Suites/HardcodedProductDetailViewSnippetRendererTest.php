<?php

namespace Brera\PoC;

use Brera\PoC\Product\Product;

class HardcodedProductDetailViewSnippetRendererTest
    extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HardcodedProductDetailViewSnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetResultList;
    private $mockKeyGenerator;

    public function setUp()
    {
        $this->mockKeyGenerator
            = $this->getMock(HardcodedProductDetailViewSnippetKeyGenerator::class,
            array('getKey'));

        $this->mockKeyGenerator->expects($this->any())->method('getKey')
            ->willReturn('test');

        $this->mockSnippetResultList = $this->getMock(SnippetResultList::class);

        $this->snippetRenderer = new HardcodedProductDetailViewSnippetRenderer(
            $this->mockSnippetResultList,
            $this->mockKeyGenerator
        );
    }

    /**
     * @test
     */
    public function itShouldBeASnippetRenderer()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function itShouldOnlyAcceptProductsForRendering()
    {
        $stubEnvironment = $this->getMockBuilder(VersionedEnvironment::class)
            ->disableOriginalConstructor()->getMock();

        $this->snippetRenderer->render(new \stdClass(), $stubEnvironment);
    }

    /**
     * @test
     */
    public function itShouldReturnASnippetResultList()
    {
        $stubProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()->getMock();
        $stubEnvironment = $this->getMockBuilder(VersionedEnvironment::class)
            ->disableOriginalConstructor()->getMock();

        $result = $this->snippetRenderer->render($stubProduct,
            $stubEnvironment);
        $this->assertSame($this->mockSnippetResultList, $result);
    }

    /**
     * @test
     */
    public function itShouldAddOneOrMoreSnippetToTheSnippetList()
    {
        $stubProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()->getMock();
        $stubEnvironment = $this->getMockBuilder(VersionedEnvironment::class)
            ->disableOriginalConstructor()->getMock();

        $this->mockSnippetResultList->expects($this->atLeastOnce())
            ->method('add')->with($this->isInstanceOf(SnippetResult::class));

        $this->snippetRenderer->render($stubProduct,
            $stubEnvironment);
    }

    public function itShouldRenderAProductDetailView()
    {
        // TODO
        $this->markTestIncomplete();
    }
}
