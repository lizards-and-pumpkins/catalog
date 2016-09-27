<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\Exception\ProductListingAttributeNotFoundException;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingBlockRenderer;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetContent
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 * @uses   \LizardsAndPumpkins\Import\SnippetContainer
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class ProductListingSnippetRendererTest extends \PHPUnit_Framework_TestCase
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

    private function createStubProductListing() : \PHPUnit_Framework_MockObject_MockObject
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

    /**
     * @param string $metaSnippetKey
     * @param string $htmlHeadMetaKey
     */
    private function prepareKeyGeneratorsForProductListing($metaSnippetKey, $htmlHeadMetaKey)
    {
        $this->stubMetaSnippetKeyGenerator->method('getKeyForContext')->willReturn($metaSnippetKey);
        $this->stubHtmlHeadMetaKeyGenerator->method('getKeyForContext')->willReturn($htmlHeadMetaKey);
    }

    protected function setUp()
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

    public function testReturnsProductListingHtmlHeadMetaSnippet()
    {
        $testMetaDescription = 'META DESCRIPTION FOR LISTING';
        $testMetaKeywords = 'meta keywords for listing';
        $htmlHeadMetaKey = 'meta_description';
        $this->prepareKeyGeneratorsForProductListing('listing', $htmlHeadMetaKey);

        $stubProductListing = $this->createStubProductListing();
        $stubProductListing->method('getUrlKey')->willReturn('listing.html');
        $stubProductListing->method('hasAttribute')->willReturn(true);
        $stubProductListing->method('getAttributeValueByCode')->willReturnMap(
            [
                ['meta_description', $testMetaDescription],
                ['meta_keywords', $testMetaKeywords],
            ]
        );
        $result = $this->renderer->render($stubProductListing);

        $metaDescriptionSnippet = $this->findSnippetByKey($htmlHeadMetaKey, ...$result);
        $this->assertInstanceOf(Snippet::class, $metaDescriptionSnippet);

        $this->assertContains(
            "<meta name=\"description\" content=\"$testMetaDescription\" />",
            $metaDescriptionSnippet->getContent()
        );

        $this->assertContains(
            "<meta name=\"keywords\" content=\"$testMetaKeywords\" />",
            $metaDescriptionSnippet->getContent()
        );
    }

    public function testFillsConainerSnippets()
    {
        $testSnippetKey = 'listing';
        $htmlHeadMetaKey = 'dummy_meta_key';
        $this->prepareKeyGeneratorsForProductListing($testSnippetKey, $htmlHeadMetaKey);

        $stubProductListing = $this->createStubProductListing();
        $stubProductListing->method('getAttributeValueByCode')->willReturn('meta_description_value');
        $stubProductListing->method('hasAttribute')->willReturn(true);
        $result = $this->renderer->render($stubProductListing);

        $metaSnippet = $this->findSnippetByKey($testSnippetKey, ...$result);
        $htmlHeadMetaKeySnippet = $this->findSnippetByKey($htmlHeadMetaKey, ...$result);

        $this->assertContains(
            '<meta name="description" content="meta_description_value" />',
            $htmlHeadMetaKeySnippet->getContent()
        );
        $listingDescriptionSnippetKey = ProductListingDescriptionSnippetRenderer::CODE;
        $canonicalTagSnippetKey = ProductListingSnippetRenderer::CANONICAL_TAG_KEY;
        $htmlHeadSnippetKey = ProductListingSnippetRenderer::HTML_HEAD_META_KEY;

        $this->assertContainerContainsSnippet($metaSnippet, 'title', ProductListingTitleSnippetRenderer::CODE);
        $this->assertContainerContainsSnippet($metaSnippet, 'sidebar_container', $listingDescriptionSnippetKey);
        $this->assertContainerContainsSnippet($metaSnippet, 'head_container', $canonicalTagSnippetKey);
        $this->assertContainerContainsSnippet($metaSnippet, 'head_container', $htmlHeadSnippetKey);
    }

    public function testProductListingDoesNotThrowExceptionOnUndefinedMetaDescription()
    {
        $testSnippetKey = 'listing';
        $htmlHeadMetaKey = 'dummy_meta_key';
        $this->prepareKeyGeneratorsForProductListing($testSnippetKey, $htmlHeadMetaKey);

        $stubProductListing = $this->createStubProductListing();
        $stubProductListing->method('getAttributeValueByCode')->willThrowException(
            new ProductListingAttributeNotFoundException(
                sprintf('Product list attribute with code "meta_description" is not found.')
            )
        );
        $result = $this->renderer->render($stubProductListing);

        $htmlHeadMetaKeySnippet = $this->findSnippetByKey($htmlHeadMetaKey, ...$result);
        $this->assertContains(
            '<meta name="description" content="" />',
            $htmlHeadMetaKeySnippet->getContent()
        );
    }

    public function testProductListingDoesNotThrowExceptionOnUndefinedMetaKeywords()
    {
        $testSnippetKey = 'listing';
        $htmlHeadMetaKey = 'dummy_meta_key';
        $this->prepareKeyGeneratorsForProductListing($testSnippetKey, $htmlHeadMetaKey);

        $stubProductListing = $this->createStubProductListing();
        $stubProductListing->method('getAttributeValueByCode')->willThrowException(
            new ProductListingAttributeNotFoundException(
                sprintf('Product list attribute with code "meta_description" is not found.')
            )
        );
        $result = $this->renderer->render($stubProductListing);

        $htmlHeadMetaKeySnippet = $this->findSnippetByKey($htmlHeadMetaKey, ...$result);
        $this->assertContains(
            '<meta name="keywords" content="" />',
            $htmlHeadMetaKeySnippet->getContent()
        );
    }

    public function testEncodesTheMetaTagValues()
    {
        $testMetaContent = 'some content that needs to be escaped: <"&';
        $expectedContent = htmlspecialchars($testMetaContent);

        $htmlHeadMetaKey = 'meta_description';
        $this->prepareKeyGeneratorsForProductListing('listing', $htmlHeadMetaKey);

        $stubProductListing = $this->createStubProductListing();
        $stubProductListing->method('getUrlKey')->willReturn('listing.html');
        $stubProductListing->method('hasAttribute')->willReturn(true);
        $stubProductListing->method('getAttributeValueByCode')->willReturn($testMetaContent);

        $result = $this->renderer->render($stubProductListing);

        $metaDescriptionSnippet = $this->findSnippetByKey($htmlHeadMetaKey, ...$result);

        $this->assertContains(
            "<meta name=\"description\" content=\"$expectedContent\" />",
            $metaDescriptionSnippet->getContent()
        );

        $this->assertContains(
            "<meta name=\"keywords\" content=\"$expectedContent\" />",
            $metaDescriptionSnippet->getContent()
        );
    }
}
