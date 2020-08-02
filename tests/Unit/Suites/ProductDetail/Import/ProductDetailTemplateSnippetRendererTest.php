<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Import\TemplateRendering\TemplateProjectionData;
use LizardsAndPumpkins\Import\TemplateRendering\TemplateSnippetRenderer;
use LizardsAndPumpkins\Import\SnippetRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\Import\ProductDetailTemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class ProductDetailTemplateSnippetRendererTest extends TestCase
{
    /**
     * @var ProductDetailTemplateSnippetRenderer
     */
    private $renderer;

    /**
     * @var TemplateSnippetRenderer|MockObject
     */
    private $mockTemplateSnippetRenderer;

    final protected function setUp(): void
    {
        $this->mockTemplateSnippetRenderer = $this->createMock(TemplateSnippetRenderer::class);
        $this->renderer = new ProductDetailTemplateSnippetRenderer($this->mockTemplateSnippetRenderer);
    }

    public function testisSnippetRenderer(): void
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testDelegatesSnippetRenderingToTemplateSnippetRenderer(): void
    {
        /** @var TemplateProjectionData|MockObject $dummyTemplateProjectionData */
        $dummyTemplateProjectionData = $this->createMock(TemplateProjectionData::class);

        $this->mockTemplateSnippetRenderer->expects($this->once())->method('render')
            ->with($dummyTemplateProjectionData);

        $this->renderer->render($dummyTemplateProjectionData);
    }
}
