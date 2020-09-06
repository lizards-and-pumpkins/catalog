<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\Exception\InvalidDataObjectTypeException;
use LizardsAndPumpkins\Import\SnippetRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\TemplateRendering\TemplateSnippetRenderer
 */
class TemplateSnippetRendererTest extends TestCase
{
    /**
     * @var TemplateSnippetRenderer
     */
    private $renderer;

    final protected function setUp(): void
    {
        /** @var BlockRenderer|MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->createMock(BlockRenderer::class);
        $stubBlockRenderer->method('render')->willReturn('');

        /** @var SnippetKeyGenerator|MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn('foo');

        /** @var ContextSource|MockObject $stubContextSource */
        $stubContextSource = $this->createMock(ContextSource::class);
        $stubContextSource->method('getAllAvailableContexts')->willReturn([$this->createMock(Context::class)]);

        $this->renderer = new TemplateSnippetRenderer($stubSnippetKeyGenerator, $stubBlockRenderer, $stubContextSource);
    }

    public function testIsSnippetRenderer(): void
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testThrowsExceptionIfDataObjectIsNotTemplateProjectionData(): void
    {
        $this->expectException(InvalidDataObjectTypeException::class);
        $this->expectExceptionMessage('Data object must be TemplateProjectionData, got string.');

        $this->renderer->render('foo');
    }

    public function testArrayOfSnippetsIsReturned(): void
    {
        /** @var TemplateProjectionData|MockObject $dataObject */
        $dataObject = $this->createMock(TemplateProjectionData::class);
        $result = $this->renderer->render($dataObject);

        $this->assertContainsOnly(Snippet::class, $result);
    }
}
