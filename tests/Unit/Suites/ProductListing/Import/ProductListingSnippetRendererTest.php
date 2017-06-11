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
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetContent
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 * @uses   \LizardsAndPumpkins\Import\SnippetContainer
 * @uses   \LizardsAndPumpkins\Import\Product\UrlKey\UrlKey
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl
 * @uses   \LizardsAndPumpkins\Import\SnippetCode
 */
class ProductListingSnippetRendererTest extends TestCase
{
    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubMetaSnippetKeyGenerator;

    /**
     * @var ProductListingSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubHtmlHeadMetaKeyGenerator;

    /**
     * @return ProductListing|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductListing() : ProductListing
    {
        $stubSearchCriteria = $this->createMock(CompositeSearchCriterion::class);
        $stubProductListing = $this->createMock(ProductListing::class);
        $stubProductListing->method('getContextData')->willReturn([]);
        $stubProductListing->method('getCriteria')->willReturn($stubSearchCriteria);

        return $stubProductListing;
    }

    /**
     * @param string $snippetKey
     * @param Snippet[] $result
     * @return Snippet|null
     */
    private function findSnippetByKey(string $snippetKey, Snippet ...$result)
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
    private function prepareKeyGeneratorsForProductListing($metaSnippetKey, $htmlHeadMetaKey)
    {
        $this->stubMetaSnippetKeyGenerator->method('getKeyForContext')->willReturn($metaSnippetKey);
        $this->stubHtmlHeadMetaKeyGenerator->method('getKeyForContext')->willReturn($htmlHeadMetaKey);
    }

    public function testThrowsExceptionIfDataObjectIsNotProductListing()
    {
        $this->expectException(InvalidDataObjectTypeException::class);
        $this->expectExceptionMessage('Data object must be ProductListing, got string.');

        $this->renderer->render('foo');
    }

    final protected function setUp()
    {
        /** @var ProductListingBlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubListingBlockRenderer */
        $stubListingBlockRenderer = $this->createMock(ProductListingBlockRenderer::class);
        $stubListingBlockRenderer->method('render')->willReturn('dummy content');
        $stubListingBlockRenderer->method('getRootSnippetCode')->willReturn('dummy root block code');
        $stubListingBlockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        $this->stubMetaSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->stubHtmlHeadMetaKeyGenerator = $this->createMock(SnippetKeyGenerator::class);

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $stubContextBuilder */
        $stubContextBuilder = $this->createMock(ContextBuilder::class);
        $stubContextBuilder->method('createContext')->willReturn($this->createMock(Context::class));

        $this->renderer = new ProductListingSnippetRenderer(
            $stubListingBlockRenderer,
            $this->stubMetaSnippetKeyGenerator,
            $stubContextBuilder,
            $this->stubHtmlHeadMetaKeyGenerator
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testSnippetWithValidJsonAsContentInAListIsReturned()
    {
        $metaSnippetKey = 'foo';
        $this->prepareKeyGeneratorsForProductListing($metaSnippetKey, 'dummy_meta_key');

        $stubProductListing = $this->createStubProductListing();
        $result = $this->renderer->render($stubProductListing);

        $metaSnippet = $this->findSnippetByKey($metaSnippetKey, ...$result);
        $pageData = json_decode($metaSnippet->getContent(), true);
        $this->assertSame('product_listing', $pageData[PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE]);
        $this->assertContains('product_listing', $pageData[PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES]);
    }
}
