<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKey;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ProductListingCanonicalTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 * @uses   \LizardsAndPumpkins\Import\Product\UrlKey\UrlKey
 */
class ProductListingCanonicalTagSnippetRendererTest extends TestCase
{
    /**
     * @var ProductListingCanonicalTagSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubCanonicalTagSnippetKeyGenerator;

    /**
     * @var BaseUrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubBaseUrlBuilder;

    private function assertContainsSnippetWithKey(string $expectedSnippetKey, Snippet ...$result)
    {
        $found = array_reduce($result, function ($found, Snippet $snippet) use ($expectedSnippetKey) {
            return $found || $snippet->getKey() === $expectedSnippetKey;
        }, false);
        $this->assertTrue($found, sprintf('No snippet with the key "%s" found in Snippet array', $expectedSnippetKey));
    }

    /**
     * @param string $snippetKey
     * @param Snippet[] $result
     * @return Snippet|null
     */
    private function findSnippetByKey(string $snippetKey, Snippet ...$result)
    {
        return array_reduce($result, function ($carry, Snippet $snippet) use ($snippetKey) {
            if (isset($carry)) {
                return $carry;
            }
            return $snippet->getKey() === $snippetKey ?
                $snippet :
                null;
        });
    }

    protected function setUp()
    {
        $this->stubCanonicalTagSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->stubBaseUrlBuilder = $this->createMock(BaseUrlBuilder::class);

        $stubContext = $this->createMock(Context::class);

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $stubContextBuilder */
        $stubContextBuilder = $this->createMock(ContextBuilder::class);
        $stubContextBuilder->method('createContext')->willReturn($stubContext);

        $this->renderer = new ProductListingCanonicalTagSnippetRenderer(
            $this->stubCanonicalTagSnippetKeyGenerator,
            $this->stubBaseUrlBuilder,
            $stubContextBuilder
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testProductListingCanonicalTagSnippetIsReturned()
    {
        $testUrlKey = UrlKey::fromString('test.html');
        $testSnippetKey = 'canonical_tag';
        $testBaseUrl = 'https://example.com/';

        $this->stubCanonicalTagSnippetKeyGenerator->method('getKeyForContext')->willReturn($testSnippetKey);

        $this->stubBaseUrlBuilder->method('create')->willReturn(new HttpBaseUrl($testBaseUrl));

        /** @var ProductListing|\PHPUnit_Framework_MockObject_MockObject $stubProductListing */
        $stubProductListing = $this->createMock(ProductListing::class);
        $stubProductListing->method('getContextData')->willReturn([]);
        $stubProductListing->method('getUrlKey')->willReturn($testUrlKey);

        $result = $this->renderer->render($stubProductListing);

        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Snippet::class, $result);
        $this->assertContainsSnippetWithKey($testSnippetKey, ...$result);

        $snippet = $this->findSnippetByKey($testSnippetKey, ...$result);

        $expectedSnippetContent = sprintf('<link rel="canonical" href="%s%s" />', $testBaseUrl, $testUrlKey);

        $this->assertSame($expectedSnippetContent, $snippet->getContent());
    }
}
