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
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchRequestHandler;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchResultMetaSnippetContent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductSearchResultMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchResultMetaSnippetContent
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class ProductSearchResultMetaSnippetRendererTest extends TestCase
{
    /**
     * @var string
     */
    private $dummySnippetKey = 'foo';

    /**
     * @var string
     */
    private $dummyRootSnippetCode = 'bar';

    /**
     * @var ProductSearchResultMetaSnippetRenderer
     */
    private $renderer;

    final protected function setUp(): void
    {
        /** @var SnippetKeyGenerator|MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummySnippetKey);

        /** @var BlockRenderer|MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->createMock(BlockRenderer::class);
        $stubBlockRenderer->method('getRootSnippetCode')->willReturn($this->dummyRootSnippetCode);
        $stubBlockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        $stubContext = $this->createMock(Context::class);
        /** @var ContextSource|MockObject $stubContextSource */
        $stubContextSource = $this->createMock(ContextSource::class);
        $stubContextSource->method('getAllAvailableContextsWithVersionApplied')->willReturn([$stubContext]);

        $this->renderer = new ProductSearchResultMetaSnippetRenderer(
            $stubSnippetKeyGenerator,
            $stubBlockRenderer,
            $stubContextSource
        );
    }

    public function testSnippetRendererInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testArrayOfSnippetsIsReturned(): void
    {
        $dummyDataObject = $this->createMock(TemplateProjectionData::class);
        $result = $this->renderer->render($dummyDataObject);

        $this->assertNotEmpty($result);
        $this->assertContainsOnly(Snippet::class, $result);
    }

    public function testSnippetWithValidJsonAsContentAddedToList(): void
    {
        $expectedSnippetContent = [
            ProductSearchResultMetaSnippetContent::KEY_HANDLER_CODE => ProductSearchRequestHandler::CODE,
            ProductSearchResultMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => $this->dummyRootSnippetCode,
            ProductSearchResultMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [$this->dummyRootSnippetCode],
            ProductSearchResultMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
            ProductSearchResultMetaSnippetContent::KEY_PAGE_SPECIFIC_DATA => [],
        ];
        $expectedSnippet = Snippet::create($this->dummySnippetKey, json_encode($expectedSnippetContent));

        $stubDataObject = $this->createMock(TemplateProjectionData::class);
        $result = $this->renderer->render($stubDataObject);

        $this->assertEquals([$expectedSnippet], $result);
    }
}
