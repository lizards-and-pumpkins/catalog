<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\Import\Exception\InvalidDataObjectTypeException;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingBlockRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingMetaSnippetContent
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 * @uses   \LizardsAndPumpkins\Import\SnippetContainer
 * @uses   \LizardsAndPumpkins\Import\Product\UrlKey\UrlKey
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingAttributeList
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class ProductListingMetaSnippetRendererTest extends TestCase
{
    /**
     * @var SnippetKeyGenerator
     */
    private $stubMetaSnippetKeyGenerator;

    /**
     * @var ProductListingMetaSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetKeyGenerator
     */
    private $stubHtmlHeadMetaKeyGenerator;

    private function findSnippetByKey(string $snippetKey, Snippet ...$result): ?Snippet
    {
        return array_reduce($result, function ($carry, Snippet $snippet) use ($snippetKey) {
            if ($carry) {
                return $carry;
            }

            return $snippet->getKey() === $snippetKey ?
                $snippet :
                null;
        });
    }

    /**
     * @param string $metaSnippetKey
     * @param string $htmlHeadMetaKey
     */
    private function prepareKeyGeneratorsForProductListing($metaSnippetKey, $htmlHeadMetaKey): void
    {
        $this->stubMetaSnippetKeyGenerator->method('getKeyForContext')->willReturn($metaSnippetKey);
        $this->stubHtmlHeadMetaKeyGenerator->method('getKeyForContext')->willReturn($htmlHeadMetaKey);
    }

    public function testThrowsExceptionIfDataObjectIsNotProductListing(): void
    {
        $this->expectException(InvalidDataObjectTypeException::class);
        $this->expectExceptionMessage('Data object must be ProductListing, got string.');

        $this->renderer->render('foo');
    }

    final protected function setUp(): void
    {
        /** @var ProductListingBlockRenderer|MockObject $stubListingBlockRenderer */
        $stubListingBlockRenderer = $this->createMock(ProductListingBlockRenderer::class);
        $stubListingBlockRenderer->method('render')->willReturn('dummy content');
        $stubListingBlockRenderer->method('getRootSnippetCode')->willReturn('dummy root block code');
        $stubListingBlockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        $this->stubMetaSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->stubHtmlHeadMetaKeyGenerator = $this->createMock(SnippetKeyGenerator::class);

        /** @var ContextBuilder|MockObject $stubContextBuilder */
        $stubContextBuilder = $this->createMock(ContextBuilder::class);
        $stubContextBuilder->method('createContext')->willReturn($this->createMock(Context::class));

        $this->renderer = new ProductListingMetaSnippetRenderer(
            $stubListingBlockRenderer,
            $this->stubMetaSnippetKeyGenerator,
            $stubContextBuilder
        );
    }

    public function testIsSnippetRenderer(): void
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testReturnsSnippetWithValidJsonAsContent(): void
    {
        $metaSnippetKey = 'foo';
        $this->prepareKeyGeneratorsForProductListing($metaSnippetKey, 'dummy_meta_key');

        $productListingAttributes = ['bar' => 'baz'];
        $productListingAttributeList = ProductListingAttributeList::fromArray($productListingAttributes);

        /** @var ProductListing|MockObject $stubProductListing */
        $stubProductListing = $this->createMock(ProductListing::class);
        $stubProductListing->method('getContextData')->willReturn([]);
        $stubProductListing->method('getCriteria')->willReturn($this->createMock(CompositeSearchCriterion::class));
        $stubProductListing->method('getAttributesList')->willReturn($productListingAttributeList);

        $result = $this->renderer->render($stubProductListing);

        $metaSnippet = $this->findSnippetByKey($metaSnippetKey, ...$result);
        $pageData = json_decode($metaSnippet->getContent(), true);

        $expectedPageSpecificData = ['product_listing_attributes' => $productListingAttributes];

        $this->assertSame('product_listing', $pageData[PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE]);
        $this->assertTrue(in_array('product_listing', $pageData[PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES]));
        $this->assertSame($expectedPageSpecificData, $pageData[PageMetaInfoSnippetContent::KEY_PAGE_SPECIFIC_DATA]);
    }
}
