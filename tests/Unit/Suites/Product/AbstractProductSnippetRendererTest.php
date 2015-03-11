<?php

namespace Brera\Product;

use Brera\ProjectionSourceData;
use Brera\SampleContextSource;
use Brera\SnippetRenderer;
use Brera\SnippetResultList;

abstract class AbstractProductSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetRenderer
     */
    protected $snippetRenderer;

    /**
     * @var SampleContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockContextSource;

    /**
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockSnippetResultList;

    /**
     * @test
     */
    public function itShouldImplementSnippetRenderer()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
    }

    /**
     * @test
     * @expectedException \Brera\Product\InvalidArgumentException
     */
    public function itShouldOnlyAcceptProductsForRendering()
    {
        $invalidSourceObject = $this->getMock(ProjectionSourceData::class, [], [], '', false);

        $this->snippetRenderer->render($invalidSourceObject, $this->mockContextSource);
    }

    /**
     * @test
     */
    public function itShouldReturnASnippetResultList()
    {
        $stubProductSource = $this->getStubProductSource();

        $result = $this->snippetRenderer->render($stubProductSource, $this->mockContextSource);
        $this->assertSame($this->mockSnippetResultList, $result);
    }

    /**
     * @test
     */
    public function itShouldMergeMoreSnippetsToTheSnippetList()
    {
        $stubProductSource = $this->getStubProductSource();

        $this->mockSnippetResultList->expects($this->atLeastOnce())
            ->method('merge')
            ->with($this->isInstanceOf(SnippetResultList::class));

        $this->snippetRenderer->render($stubProductSource, $this->mockContextSource);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ProductSource
     */
    private function getStubProductSource()
    {
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->expects($this->any())
            ->method('getId')
            ->willReturn($stubProductId);

        $stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);
        $stubProductSource->expects($this->any())
            ->method('getId')
            ->willReturn($stubProductId);
        $stubProductSource->expects($this->any())
            ->method('getProductForContext')
            ->willReturn($stubProduct);

        return $stubProductSource;
    }
}
