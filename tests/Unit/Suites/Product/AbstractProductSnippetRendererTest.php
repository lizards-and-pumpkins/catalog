<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\ProjectionSourceData;
use Brera\SampleContextSource;
use Brera\SnippetRenderer;
use Brera\SnippetResultList;

abstract class AbstractProductSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetResultList;

    /**
     * @var SampleContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContextSource;

    protected function setUp()
    {
        $this->initMockContextSource();
        $this->initMockSnippetResultList();

        $this->snippetRenderer = $this->createSnippetRendererUnderTest();
    }

    /**
     * @return SnippetRenderer
     */
    abstract protected function createSnippetRendererUnderTest();

    /**
     * @return SnippetRenderer
     */
    protected function getSnipperRendererUnderTest()
    {
        return $this->snippetRenderer;
    }
    
    /**
     * @return SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockSnippetResultList()
    {
        return $this->mockSnippetResultList;
    }

    /**
     * @test
     */
    public function itShouldImplementSnippetRenderer()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
    }

    /**
     * @test
     * @expectedException \Brera\InvalidProjectionDataSourceTypeException
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
     * @param string $rendererClass
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getProductInContextRendererMock($rendererClass)
    {
        $mockProductInContextRenderer = $this->getMock($rendererClass, [], [], '', false);
        $mockProductInContextRenderer->expects($this->any())
            ->method('render')
            ->willReturn($this->mockSnippetResultList);
        $mockProductInContextRenderer->expects($this->any())
            ->method('getContextParts')
            ->willReturn(['version']);

        return $mockProductInContextRenderer;
    }

    protected function initMockContextSource()
    {
        $stubContext = $this->getMock(Context::class, [], [], '', false);

        $this->mockContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $this->mockContextSource->expects($this->any())
            ->method('getAllAvailableContexts')
            ->willReturn([$stubContext]);
    }

    protected function initMockSnippetResultList()
    {
        $this->mockSnippetResultList = $this->getMock(SnippetResultList::class);
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
