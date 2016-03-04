<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\BaseUrl\HttpBaseUrl;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductCanonicalTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\BaseUrl\HttpBaseUrl
 * @uses   \LizardsAndPumpkins\Snippet
 */
class ProductCanonicalTagSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductCanonicalTagSnippetRenderer
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

    /**
     * @var ProductView|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductView;

    /**
     * @param string $expectedSnippetKey
     * @param Snippet[] $result
     */
    private function assertContainsSnippetWithKey($expectedSnippetKey, array $result)
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
    private function findSnippetByKey($snippetKey, array $result)
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
        $this->stubCanonicalTagSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubBaseUrlBuilder = $this->getMock(BaseUrlBuilder::class);
        $this->stubBaseUrlBuilder->method('create')->willReturn(HttpBaseUrl::fromString('https://example.com/'));
        $this->renderer = new ProductCanonicalTagSnippetRenderer(
            $this->stubCanonicalTagSnippetKeyGenerator,
            $this->stubBaseUrlBuilder
        );

        $this->mockProductView = $this->getMock(ProductView::class);
        $this->mockProductView->method('getFirstValueOfAttribute')->willReturn('test.html');
        $this->mockProductView->method('getContext')->willReturn($this->getMock(Context::class));
    }

    public function testImplementsTheSnipperRendererInterface()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testReturnsACanonicalTagSnippet()
    {
        $snippetKey = 'canonical_tag';
        $this->stubCanonicalTagSnippetKeyGenerator->method('getKeyForContext')->willReturn($snippetKey);

        $result = $this->renderer->render($this->mockProductView);

        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
        $this->assertContainsOnlyInstancesOf(Snippet::class, $result);
        $this->assertContainsSnippetWithKey($snippetKey, $result);

        $snippet = $this->findSnippetByKey($snippetKey, $result);

        $this->assertSame('<link rel="canonical" href="https://example.com/test.html" />', $snippet->getContent());
    }
}
