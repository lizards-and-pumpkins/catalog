<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingRobotsMetaTagSnippetRenderer
 */
class ProductListingRobotsMetaTagSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingRobotsMetaTagSnippetRenderer
     */
    private $renderer;

    /**
     * @var RobotsMetaTagSnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRobotsMetaTagRenderer;

    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextBuilder;

    protected function setUp()
    {
        $this->stubRobotsMetaTagRenderer = $this->getMock(RobotsMetaTagSnippetRenderer::class, [], [], '', false);
        $this->stubContextBuilder = $this->getMock(ContextBuilder::class);
        $this->stubContextBuilder->method('createContext')->willReturn($this->getMock(Context::class));

        $this->renderer = new ProductListingRobotsMetaTagSnippetRenderer(
            $this->stubRobotsMetaTagRenderer,
            $this->stubContextBuilder
        );
    }

    public function testImplementsSnippetRenderer()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testItDelegatesToTheRobotsMetaTagSnippetRenderer()
    {
        /** @var ProductListing|\PHPUnit_Framework_MockObject_MockObject $stubProductListing */
        $stubProductListing = $this->getMock(ProductListing::class, [], [], '', false);
        $stubProductListing->method('getContextData')->willReturn([]);
        $dummyReturnValue = ['dummy snippets'];
        $this->stubRobotsMetaTagRenderer->expects($this->once())
            ->method('render')
            ->with($this->stubContextBuilder->createContext([]))
            ->willReturn($dummyReturnValue);
        
        $this->assertSame($dummyReturnValue, $this->renderer->render($stubProductListing));
    }
}
