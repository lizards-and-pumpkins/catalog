<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingBlockRenderer;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\TemplateProjectionData;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class ProductListingTemplateSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingTemplateSnippetRenderer
     */
    private $renderer;

    protected function setUp()
    {
        /** @var ProductListingBlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->createMock(ProductListingBlockRenderer::class);
        $stubBlockRenderer->method('render')->willReturn('');

        $dummySnippetKey = 'foo';

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($dummySnippetKey);

        $stubContext = $this->createMock(Context::class);

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->createMock(ContextSource::class);
        $stubContextSource->method('getAllAvailableContexts')->willReturn([$stubContext]);

        $this->renderer = new ProductListingTemplateSnippetRenderer(
            $stubSnippetKeyGenerator,
            $stubBlockRenderer,
            $stubContextSource
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testArrayOfSnippetsIsReturned()
    {
        /** @var TemplateProjectionData|\PHPUnit_Framework_MockObject_MockObject $dataObject */
        $dataObject = $this->createMock(TemplateProjectionData::class);
        $result = $this->renderer->render($dataObject);

        $this->assertContainsOnly(Snippet::class, $result);
    }
}
