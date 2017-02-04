<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\RobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\Import\SnippetRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\ProductDetailPageRobotsMetaTagSnippetRenderer
 */
class ProductDetailPageRobotsMetaTagSnippetRendererTest extends TestCase
{
    /**
     * @var ProductDetailPageRobotsMetaTagSnippetRenderer
     */
    private $renderer;

    /**
     * @var RobotsMetaTagSnippetRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRobotsMetaTagRenderer;

    protected function setUp()
    {
        $this->mockRobotsMetaTagRenderer = $this->createMock(RobotsMetaTagSnippetRenderer::class);
        $this->renderer = new ProductDetailPageRobotsMetaTagSnippetRenderer($this->mockRobotsMetaTagRenderer);
    }

    public function testIsASnippetRenderer()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testDelegatesToRobotsMetaTagSnippetRenderer()
    {
        $stubContext = $this->createMock(Context::class);
        /** @var ProductView|\PHPUnit_Framework_MockObject_MockObject $stubProductView */
        $stubProductView = $this->createMock(ProductView::class);
        $stubProductView->method('getContext')->willReturn($stubContext);

        $dummyReturnValue = ['dummy'];
        $this->mockRobotsMetaTagRenderer->expects($this->once())
            ->method('render')
            ->with($stubContext)
            ->willReturn($dummyReturnValue);
        
        $this->assertSame($dummyReturnValue, $this->renderer->render($stubProductView));
    }
}
