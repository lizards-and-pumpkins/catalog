<?php

namespace Brera\Product;

use Brera\SampleContextSource;
use Brera\Context\Context;
use Brera\SnippetResultList;

/**
 * @covers \Brera\Product\ProductSourceDetailViewSnippetRenderer
 */
class ProductSourceDetailViewSnippetRendererTest extends AbstractProductSnippetRendererTest
{
    protected function setUp()
    {
        $this->mockSnippetResultList = $this->getMock(SnippetResultList::class);

        $rendererClass = ProductInContextDetailViewSnippetRenderer::class;
        $mockProductInContextDetailViewRenderer = $this->getMock($rendererClass, [], [], '', false);
        $mockProductInContextDetailViewRenderer->expects($this->any())
            ->method('render')
            ->willReturn($this->mockSnippetResultList);
        $mockProductInContextDetailViewRenderer->expects($this->any())
            ->method('getContextParts')
            ->willReturn(['version']);

        $this->snippetRenderer = new ProductSourceDetailViewSnippetRenderer(
            $this->mockSnippetResultList,
            $mockProductInContextDetailViewRenderer
        );

        $stubContext = $this->getMock(Context::class, [], [], '', false);

        $this->mockContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $this->mockContextSource->expects($this->any())
            ->method('getAllAvailableContexts')
            ->willReturn([$stubContext]);
    }
}
