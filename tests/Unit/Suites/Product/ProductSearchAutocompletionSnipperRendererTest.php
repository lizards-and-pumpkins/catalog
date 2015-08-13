<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\Renderer\BlockRenderer;
use Brera\RootSnippetSourceList;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

/**
 * @covers \Brera\Product\ProductSearchAutocompletionSnipperRenderer
 * @uses   \Brera\Snippet
 */
class ProductSearchAutocompletionSnipperRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var ProductSearchAutocompletionSnipperRenderer
     */
    private $snippetRenderer;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource
     */
    private $stubContextSource;

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn('foo');

        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);

        $this->snippetRenderer = new ProductSearchAutocompletionSnipperRenderer(
            $this->mockSnippetList,
            $stubSnippetKeyGenerator,
            $stubBlockRenderer
        );

        $stubContext = $this->getMock(Context::class);

        $this->stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->stubContextSource->method('getAllAvailableContexts')->willReturn([$stubContext]);
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
    }

    public function testSnippetListIsReturned()
    {
        /** @var RootSnippetSourceList|\PHPUnit_Framework_MockObject_MockObject $stubRootSnippetSourceList */
        $stubRootSnippetSourceList = $this->getMock(RootSnippetSourceList::class, [], [], '', false);

        $result = $this->snippetRenderer->render($stubRootSnippetSourceList, $this->stubContextSource);

        $this->assertInstanceOf(SnippetList::class, $result);
    }

    public function testSnippetIsAddedToSnippetList()
    {
        /** @var RootSnippetSourceList|\PHPUnit_Framework_MockObject_MockObject $stubRootSnippetSourceList */
        $stubRootSnippetSourceList = $this->getMock(RootSnippetSourceList::class, [], [], '', false);

        $this->mockSnippetList->expects($this->once())->method('add')->with($this->isInstanceOf(Snippet::class));

        $this->snippetRenderer->render($stubRootSnippetSourceList, $this->stubContextSource);
    }
}
