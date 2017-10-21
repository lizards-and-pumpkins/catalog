<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\Import\Exception\InvalidDataObjectTypeException;
use LizardsAndPumpkins\Import\SnippetRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class ContentBlockSnippetRendererTest extends TestCase
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
     * @param string $contentBlockContent
     * @return ContentBlockSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubContentBlockSource(string $contentBlockContent): ContentBlockSource
    {
        $stubContentBlockSource = $this->createMock(ContentBlockSource::class);
        $stubContentBlockSource->method('getContent')->willReturn($contentBlockContent);
        $stubContentBlockSource->method('getContext')->willReturn($this->createMock(Context::class));
        $stubContentBlockSource->method('getKeyGeneratorParams')->willReturn([]);

        return $stubContentBlockSource;
    }

    final protected function setUp()
    {
        $this->stubSnippetKeyGeneratorLocator = $this->createMock(SnippetKeyGeneratorLocator::class);
        $this->renderer = new ContentBlockSnippetRenderer($this->stubSnippetKeyGeneratorLocator);
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testThrowsExceptionIfDataObjectIsNotContentBlockSource()
    {
        $this->expectException(InvalidDataObjectTypeException::class);
        $this->expectExceptionMessage('Data object must be ContentBlockSource, got string.');

        $this->renderer->render('foo');
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
