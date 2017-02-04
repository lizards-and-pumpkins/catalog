<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\RobotsMetaTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class RobotsMetaTagSnippetRendererTest extends TestCase
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
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @param string $content
     * @return Snippet|null
     */
    private function findRobotsMetaTagSnippetByContent(string $content)
    {
        $snippets = $this->renderer->render($this->stubContext);
        return $this->findSnippetByKey($snippets, $this->getDummyRobotsTagKeyBasedOnContent($content));
    }

    private function getDummyRobotsTagKeyBasedOnContent(string $robotsTagContent) : string
    {
        return str_replace([',', ' '], '', $robotsTagContent);
    }

    /**
     * @param Snippet[] $snippets
     * @param string $snippetKey
     * @return Snippet|null
     */
    private function findSnippetByKey(array $snippets, string $snippetKey)
    {
        return array_reduce($snippets, function ($carry, Snippet $snippet) use ($snippetKey) {
            return $carry ?: ($snippet->getKey() === $snippetKey ? $snippet : null);
        });
    }

    private function assertRobotsMetaTagSnippetForContent(string $expectedContent)
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
        $this->stubRobotsMetaTagSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->stubRobotsMetaTagSnippetKeyGenerator->method('getKeyForContext')
            ->willReturnCallback(function (Context $context, array $usedDataParts) {
                return $this->getDummyRobotsTagKeyBasedOnContent($usedDataParts['robots']);
            });
        $this->stubContext = $this->createMock(Context::class);
        $this->renderer = new RobotsMetaTagSnippetRenderer($this->stubRobotsMetaTagSnippetKeyGenerator);
    }

    public function testItIsASnippetRenderer()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testItReturnsAnArrayOfSnippets()
    {
        $snippets = $this->renderer->render($this->stubContext);
        
        $this->assertInternalType('array', $snippets);
        $this->assertNotEmpty($snippets);
        $this->assertContainsOnlyInstancesOf(Snippet::class, $snippets);
    }

    /**
     * @dataProvider robotsMetaTagContentProvider
     */
    public function testRobotsMetaTagIsPresent(string $expectedContent)
    {
        $this->assertRobotsMetaTagSnippetForContent($expectedContent);
    }

    /**
     * @return array[]
     */
    public function robotsMetaTagContentProvider() : array
    {
        return [
            ['all'],
            ['noindex'],
        ];
    }
}
