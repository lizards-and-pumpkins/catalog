<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Import\TemplateRendering\TemplateProjectionData;
use LizardsAndPumpkins\Import\TemplateRendering\TemplateSnippetRenderer;
use LizardsAndPumpkins\Import\SnippetRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class ProductListingTemplateSnippetRendererTest extends TestCase
{
    /**
     * @var ProductListingTemplateSnippetRenderer
     */
    private $renderer;

    /**
     * @var TemplateSnippetRenderer|MockObject
     */
    private $mockTemplateSnippetRenderer;

    final protected function setUp(): void
    {
        $this->mockTemplateSnippetRenderer = $this->createMock(TemplateSnippetRenderer::class);
        $this->renderer = new ProductListingTemplateSnippetRenderer($this->mockTemplateSnippetRenderer);
    }

    public function testSnippetRendererInterfaceIsImplemented(): void
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
