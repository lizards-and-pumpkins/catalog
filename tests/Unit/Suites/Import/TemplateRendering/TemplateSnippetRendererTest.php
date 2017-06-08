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

    final protected function setUp()
    {
        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->createMock(BlockRenderer::class);
        $stubBlockRenderer->method('render')->willReturn('');

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn('foo');

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->createMock(ContextSource::class);
        $stubContextSource->method('getAllAvailableContexts')->willReturn([$this->createMock(Context::class)]);

        $this->renderer = new TemplateSnippetRenderer($stubSnippetKeyGenerator, $stubBlockRenderer, $stubContextSource);
    }

    public function testIsSnippetRenderer()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testThrowsExceptionIfDataObjectIsNotTemplateProjectionData()
    {
        $this->expectException(InvalidDataObjectTypeException::class);
        $this->expectExceptionMessage('Data object must be TemplateProjectionData, got string.');

        $this->renderer->render('foo');
    }

    public function testArrayOfSnippetsIsReturned()
    {
        /** @var TemplateProjectionData|\PHPUnit_Framework_MockObject_MockObject $dataObject */
        $dataObject = $this->createMock(TemplateProjectionData::class);
        $result = $this->renderer->render($dataObject);

        $this->assertContainsOnly(Snippet::class, $result);
    }
}
