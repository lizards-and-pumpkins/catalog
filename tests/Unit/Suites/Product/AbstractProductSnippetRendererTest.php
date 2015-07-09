<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\ProjectionSourceData;
use Brera\SampleContextSource;
use Brera\SnippetRenderer;
use Brera\SnippetList;

abstract class AbstractProductSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var SampleContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContextSource;

    protected function setUp()
    {
        $this->initMockContextSource();
        $this->initMockSnippetList();

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
     * @return SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockSnippetList()
    {
        return $this->mockSnippetList;
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
    }

    public function testOnlyProductsAreAcceptedForRendering()
    {
        $this->setExpectedException(InvalidProjectionDataSourceTypeException::class);
        $invalidSourceObject = $this->getMock(ProjectionSourceData::class, [], [], '', false);

        $this->snippetRenderer->render($invalidSourceObject, $this->mockContextSource);
    }

    public function testSnippetListIsReturned()
    {
        $stubProductSource = $this->getStubProductSource();

        $result = $this->snippetRenderer->render($stubProductSource, $this->mockContextSource);
        $this->assertSame($this->mockSnippetList, $result);
    }

    public function testSnippetsAreMergedIntoSnippetList()
    {
        $stubProductSource = $this->getStubProductSource();

        $this->mockSnippetList->expects($this->atLeastOnce())
            ->method('merge')
            ->with($this->isInstanceOf(SnippetList::class));

        $this->snippetRenderer->render($stubProductSource, $this->mockContextSource);
    }

    /**
     * @param string $rendererClass
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getProductInContextRendererMock($rendererClass)
    {
        $mockProductInContextRenderer = $this->getMock($rendererClass, [], [], '', false);
        $mockProductInContextRenderer->method('render')
            ->willReturn($this->mockSnippetList);
        $mockProductInContextRenderer->method('getContextParts')
            ->willReturn(['version']);

        return $mockProductInContextRenderer;
    }

    protected function initMockContextSource()
    {
        $stubContext = $this->getMock(Context::class, [], [], '', false);

        $this->mockContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $this->mockContextSource->method('getAllAvailableContexts')
            ->willReturn([$stubContext]);
    }

    protected function initMockSnippetList()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ProductSource
     */
    private function getStubProductSource()
    {
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->method('getId')
            ->willReturn($stubProductId);

        $stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);
        $stubProductSource->method('getId')
            ->willReturn($stubProductId);
        $stubProductSource->method('getProductForContext')
            ->willReturn($stubProduct);

        return $stubProductSource;
    }
}
