<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\BaseUrl\HttpBaseUrl;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\Snippet;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductListingSnippetContent
 * @uses   \LizardsAndPumpkins\Snippet
 * @uses   \LizardsAndPumpkins\SnippetContainer
 * @uses   \LizardsAndPumpkins\BaseUrl\HttpBaseUrl
 */
class ProductListingSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubMetaSnippetKeyGenerator;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubCanonicalTagSnippetKeyGenerator;

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
    private function createStubProductListing()
    {
        $stubSearchCriteria = $this->getMock(CompositeSearchCriterion::class, [], [], '', false);
        $stubProductListing = $this->getMock(ProductListing::class, [], [], '', false);
        $stubProductListing->method('getContextData')->willReturn([]);
        $stubProductListing->method('getCriteria')->willReturn($stubSearchCriteria);

        return $stubProductListing;
    }

    /**
     * @param string $snippetKey
     * @param Snippet[] $result
     *
     * @return Snippet|null
     */
    private function findSnippetByKey($snippetKey, $result)
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
     * @param Snippet $metaSnippet
     * @param string $containerCode
     * @param string $expectedSnippetCode
     */
    private function assertContainerContainsSnippet(Snippet $metaSnippet, $containerCode, $expectedSnippetCode)
    {
        $pageData = json_decode($metaSnippet->getContent(), true);
        $this->assertContains(
            $expectedSnippetCode,
            $pageData[PageMetaInfoSnippetContent::KEY_CONTAINER_SNIPPETS][$containerCode]
        );
    }

    protected function setUp()
    {
        /** @var ProductListingBlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubListingBlockRenderer */
        $stubListingBlockRenderer = $this->getMock(ProductListingBlockRenderer::class, [], [], '', false);
        $stubListingBlockRenderer->method('render')->willReturn('dummy content');
        $stubListingBlockRenderer->method('getRootSnippetCode')->willReturn('dummy root block code');
        $stubListingBlockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        $this->stubMetaSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubCanonicalTagSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubHtmlHeadMetaKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $stubContextBuilder */
        $stubContextBuilder = $this->getMock(ContextBuilder::class);
        $stubContextBuilder->method('createContext')->willReturn($this->getMock(Context::class));

        $stubBaseUrlBuilder = $this->getMock(BaseUrlBuilder::class);
        $stubBaseUrlBuilder->method('create')->willReturn(HttpBaseUrl::fromString('https://example.com/'));

        $this->renderer = new ProductListingSnippetRenderer(
            $stubListingBlockRenderer,
            $this->stubMetaSnippetKeyGenerator,
            $stubContextBuilder,
            $this->stubCanonicalTagSnippetKeyGenerator,
            $stubBaseUrlBuilder,
            $this->stubHtmlHeadMetaKeyGenerator
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testSnippetWithValidJsonAsContentInAListIsReturned()
    {
        $testSnippetKey = 'foo';
        $this->stubMetaSnippetKeyGenerator->method('getKeyForContext')->willReturn($testSnippetKey);
        $this->stubCanonicalTagSnippetKeyGenerator->method('getKeyForContext')->willReturn('canonical');
        $this->stubHtmlHeadMetaKeyGenerator->method('getKeyForContext')->willReturn('dummy_meta_key');

        $stubProductListing = $this->createStubProductListing();
        $result = $this->renderer->render($stubProductListing);

        $metaSnippet = $this->findSnippetByKey($testSnippetKey, $result);
        $pageData = json_decode($metaSnippet->getContent(), true);
        $this->assertSame('product_listing', $pageData[PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE]);
        $this->assertContains('product_listing', $pageData[PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES]);
    }

    public function testReturnsProductListingCanonicalTagSnippet()
    {
        $testSnippetKey = 'canonical';
        $this->stubCanonicalTagSnippetKeyGenerator->method('getKeyForContext')->willReturn($testSnippetKey);
        $this->stubHtmlHeadMetaKeyGenerator->method('getKeyForContext')->willReturn('dummy_meta_key');
        $this->stubMetaSnippetKeyGenerator->method('getKeyForContext')->willReturn('listing');

        $stubProductListing = $this->createStubProductListing();
        $stubProductListing->method('getUrlKey')->willReturn('listing.html');
        $result = $this->renderer->render($stubProductListing);

        $canonicalTagSnippet = $this->findSnippetByKey($testSnippetKey, $result);
        $this->assertInstanceOf(Snippet::class, $canonicalTagSnippet);

        $this->assertSame(
            '<link rel="canonical" href="https://example.com/listing.html" />',
            $canonicalTagSnippet->getContent()
        );
    }

    public function testReturnsProductListingMetaDescriptionSnippet()
    {
        $testMetaDescription = 'META DESCRIPTION FOR LISTING';

        $testSnippetKey = 'meta_description';
        $this->stubHtmlHeadMetaKeyGenerator->method('getKeyForContext')->willReturn($testSnippetKey);
        $this->stubMetaSnippetKeyGenerator->method('getKeyForContext')->willReturn('listing');
        $this->stubCanonicalTagSnippetKeyGenerator->method('getKeyForContext')->willReturn('dummy_canonical_key');

        $stubProductListing = $this->createStubProductListing();
        $stubProductListing->method('getUrlKey')->willReturn('listing.html');
        $stubProductListing->method('getAttributeValueByCode')->willReturn($testMetaDescription);
        $result = $this->renderer->render($stubProductListing);

        $metaDescriptionSnippet = $this->findSnippetByKey($testSnippetKey, $result);
        $this->assertInstanceOf(Snippet::class, $metaDescriptionSnippet);

        $this->assertSame(
            '<meta name="description" content="META DESCRIPTION FOR LISTING" />',
            $metaDescriptionSnippet->getContent()
        );
    }

    public function testFillsConainerSnippets()
    {
        $testSnippetKey = 'listing';
        $this->stubCanonicalTagSnippetKeyGenerator->method('getKeyForContext')->willReturn('canonical');
        $this->stubMetaSnippetKeyGenerator->method('getKeyForContext')->willReturn($testSnippetKey);
        $this->stubHtmlHeadMetaKeyGenerator->method('getKeyForContext')->willReturn('dummy_meta_key');

        $stubProductListing = $this->createStubProductListing();
        $result = $this->renderer->render($stubProductListing);

        $metaSnippet = $this->findSnippetByKey($testSnippetKey, $result);

        $listingDescriptionSnippetKey = ProductListingDescriptionSnippetRenderer::CODE;
        $canonicalTagSnippetKey = ProductListingSnippetRenderer::CANONICAL_TAG_KEY;
        $htmlHeadSnippetKey = ProductListingSnippetRenderer::HTML_HEAD_META_KEY;
        $this->assertContainerContainsSnippet($metaSnippet, 'title', ProductListingTitleSnippetRenderer::CODE);
        $this->assertContainerContainsSnippet($metaSnippet, 'sidebar_container', $listingDescriptionSnippetKey);
        $this->assertContainerContainsSnippet($metaSnippet, 'head_container', $canonicalTagSnippetKey);
        $this->assertContainerContainsSnippet($metaSnippet, 'head_container', $htmlHeadSnippetKey);
    }
}
