<?php

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;


/**
 * @covers \LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class ProductSearchAutosuggestionSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductSearchAutosuggestionSnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource
     */
    private $stubContextSource;

    protected function setUp()
    {
        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn('foo');

        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        
        $stubContext = $this->getMock(Context::class);
        $this->stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->stubContextSource->method('getAllAvailableContexts')->willReturn([$stubContext]);

        $this->snippetRenderer = new ProductSearchAutosuggestionSnippetRenderer(
            $stubSnippetKeyGenerator,
            $stubBlockRenderer,
            $this->stubContextSource
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
    }

    public function testArrayOfSnippetsIsReturned()
    {
        $dataObject = new \stdClass();
        $result = $this->snippetRenderer->render($dataObject);

        $this->assertContainsOnly(Snippet::class, $result);
    }
}
