<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;

use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingBlockRenderer;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;

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
        $stubBlockRenderer = $this->getMock(ProductListingBlockRenderer::class, [], [], '', false);

        $dummySnippetKey = 'foo';

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($dummySnippetKey);

        $stubContext = $this->getMock(Context::class);

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
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
        $dataObject = new \stdClass();
        $result = $this->renderer->render($dataObject);

        $this->assertContainsOnly(Snippet::class, $result);
    }
}
