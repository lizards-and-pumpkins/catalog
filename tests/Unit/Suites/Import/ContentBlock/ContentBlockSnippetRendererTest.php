<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\Import\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class ContentBlockSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentBlockSnippetKeyGeneratorLocatorStrategy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetKeyGeneratorLocator;

    /**
     * @var ContentBlockSnippetRenderer
     */
    private $renderer;

    /**
     * @return ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubContextBuilder() : ContextBuilder
    {
        $stubContext = $this->createMock(Context::class);
        $stubContextBuilder = $this->createMock(ContextBuilder::class);
        $stubContextBuilder->method('createContext')->willReturn($stubContext);

        return $stubContextBuilder;
    }

    /**
     * @param string $contentBlockContent
     * @return ContentBlockSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubContentBlockSource(string $contentBlockContent) : ContentBlockSource
    {
        $stubContentBlockSource = $this->createMock(ContentBlockSource::class);
        $stubContentBlockSource->method('getContent')->willReturn($contentBlockContent);
        $stubContentBlockSource->method('getContextData')->willReturn([]);
        $stubContentBlockSource->method('getKeyGeneratorParams')->willReturn([]);

        return $stubContentBlockSource;
    }

    protected function setUp()
    {
        $this->stubSnippetKeyGeneratorLocator = $this->createMock(SnippetKeyGeneratorLocator::class);
        $stubContextBuilder = $this->createStubContextBuilder();

        $this->renderer = new ContentBlockSnippetRenderer($this->stubSnippetKeyGeneratorLocator, $stubContextBuilder);
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testSnippetIsAddedToList()
    {
        $stubSnippetKey = 'foo';
        $dummyContentBlockContent = 'bar';

        $stubContentBlockSource = $this->createStubContentBlockSource($dummyContentBlockContent);

        $stubKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $stubKeyGenerator->method('getKeyForContext')->willReturn($stubSnippetKey);

        $this->stubSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')->willReturn($stubKeyGenerator);

        $expectedSnippet = Snippet::create($stubSnippetKey, $dummyContentBlockContent);
        $result = $this->renderer->render($stubContentBlockSource);

        $this->assertEquals([$expectedSnippet], $result);
    }
}
