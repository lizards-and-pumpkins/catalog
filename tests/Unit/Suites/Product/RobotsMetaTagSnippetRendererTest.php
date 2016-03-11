<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\RobotsMetaTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 */
class RobotsMetaTagSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RobotsMetaTagSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRobotsMetaTagSnippetKeyGenerator;

    /**
     * @var ProductView|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductView;

    /**
     * @param string $content
     * @return Snippet|null
     */
    private function findRobotsMetaTagSnippetByContent($content)
    {
        $snippets = $this->renderer->render($this->stubProductView);
        return $this->findSnippetByKey($snippets, $this->getDummyRobotsTagKeyBasedOnContent($content));
    }

    /**
     * @param string $robotsTagContent
     * @return string
     */
    private function getDummyRobotsTagKeyBasedOnContent($robotsTagContent)
    {
        return str_replace([',', ' '], '', $robotsTagContent);
    }

    /**
     * @param Snippet[] $snippets
     * @param string $snippetKey
     * @return Snippet|null
     */
    private function findSnippetByKey(array $snippets, $snippetKey)
    {
        return array_reduce($snippets, function ($carry, Snippet $snippet) use ($snippetKey) {
            return $carry ?: ($snippet->getKey() === $snippetKey ? $snippet : null);
        });
    }

    /**
     * @param string $expectedContent
     */
    private function assertRobotsMetaTagSnippetForContent($expectedContent)
    {
        $snippet = $this->findRobotsMetaTagSnippetByContent($expectedContent);
        if (null === $snippet) {
            $this->fail(sprintf('No robot meta tag snippet found for content "%s"', $expectedContent));
        }
        $expectedTag = sprintf('<meta name="robots" content="%s"/>', $expectedContent);
        $message = sprintf('Robots meta tag snippet content mismatch');
        $this->assertSame($expectedTag, $snippet->getContent(), $message);
    }

    protected function setUp()
    {
        $this->stubRobotsMetaTagSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubRobotsMetaTagSnippetKeyGenerator->method('getKeyForContext')
            ->willReturnCallback(function (Context $context, array $usedDataParts) {
                return $this->getDummyRobotsTagKeyBasedOnContent($usedDataParts['robots']);
            });
        $this->renderer = new RobotsMetaTagSnippetRenderer($this->stubRobotsMetaTagSnippetKeyGenerator);
        $this->stubProductView = $this->getMock(ProductView::class);
        $this->stubProductView->method('getContext')->willReturn($this->getMock(Context::class));
    }

    public function testItIsASnippetRenderer()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testItReturnsAnArrayOfSnippets()
    {
        $snippets = $this->renderer->render($this->stubProductView);
        
        $this->assertInternalType('array', $snippets);
        $this->assertNotEmpty($snippets);
        $this->assertContainsOnlyInstancesOf(Snippet::class, $snippets);
    }

    /**
     * @param string $expectedContent
     * @dataProvider robotsMetaTagContentProvider
     */
    public function testRobotsMetaTagIsPresent($expectedContent)
    {
        $this->assertRobotsMetaTagSnippetForContent($expectedContent);
    }

    /**
     * @return array[]
     */
    public function robotsMetaTagContentProvider()
    {
        return [
            ['all'],
            ['noindex'],
        ];
    }
}
