<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;
use LizardsAndPumpkins\Import\TemplateRendering\TemplateProjectionData;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchResultMetaSnippetContent;
use LizardsAndPumpkins\Import\SnippetCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductSearchResultMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchResultMetaSnippetContent
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 * @uses   \LizardsAndPumpkins\Import\SnippetCode
 */
class ProductSearchResultMetaSnippetRendererTest extends TestCase
{
    /**
     * @var string
     */
    private $dummySnippetKey = 'foo';

    /**
     * @var SnippetCode
     */
    private $rootSnippetCode;

    /**
     * @var ProductSearchResultMetaSnippetRenderer
     */
    private $renderer;

    protected function setUp()
    {
        $this->rootSnippetCode = new SnippetCode('bar');

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummySnippetKey);

        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->createMock(BlockRenderer::class);
        $stubBlockRenderer->method('getRootSnippetCode')->willReturn($this->rootSnippetCode);
        $stubBlockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        $stubContext = $this->createMock(Context::class);
        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->createMock(ContextSource::class);
        $stubContextSource->method('getAllAvailableContextsWithVersionApplied')->willReturn([$stubContext]);

        $this->renderer = new ProductSearchResultMetaSnippetRenderer(
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
        $dummyDataObject = $this->createMock(TemplateProjectionData::class);
        $result = $this->renderer->render($dummyDataObject);

        $this->assertNotEmpty($result);
        $this->assertContainsOnly(Snippet::class, $result);
    }

    public function testSnippetWithValidJsonAsContentAddedToList()
    {
        $expectedSnippetContent = [
            ProductSearchResultMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => $this->rootSnippetCode,
            ProductSearchResultMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [$this->rootSnippetCode],
            ProductSearchResultMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ];
        $expectedSnippet = Snippet::create($this->dummySnippetKey, json_encode($expectedSnippetContent));

        $stubDataObject = $this->createMock(TemplateProjectionData::class);
        $result = $this->renderer->render($stubDataObject);

        $this->assertEquals([$expectedSnippet], $result);
    }
}
