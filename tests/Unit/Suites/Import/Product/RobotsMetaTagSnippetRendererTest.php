<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Import\Product\RobotsMetaTagSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
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
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @param string $content
     * @return Snippet|null
     */
    private function findRobotsMetaTagSnippetByContent($content)
    {
        $snippets = $this->renderer->render($this->stubContext);
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
        $this->stubContext = $this->getMock(Context::class);
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
