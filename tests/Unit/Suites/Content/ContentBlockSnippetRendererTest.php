<?php

namespace LizardsAndPumpkins\Content;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\DecoratedContextBuilder;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\ContentBlockSnippetKeyGeneratorLocatorStrategy;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Content\ContentBlockSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 */
class ContentBlockSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var ContentBlockSnippetKeyGeneratorLocatorStrategy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetKeyGeneratorLocator;

    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContextBuilder;

    /**
     * @var ContentBlockSnippetRenderer
     */
    private $renderer;

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);
        $this->stubSnippetKeyGeneratorLocator = $this->getMock(
            ContentBlockSnippetKeyGeneratorLocatorStrategy::class,
            [],
            [],
            '',
            false
        );
        $this->mockContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);

        $this->renderer = new ContentBlockSnippetRenderer(
            $this->mockSnippetList,
            $this->stubSnippetKeyGeneratorLocator,
            $this->mockContextBuilder
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testSnippetListContains()
    {
        $stubSnippetKey = 'foo';
        $dummyContentBlockContent = 'bar';
        $stubContext = $this->getMock(Context::class);

        /** @var ContentBlockSource|\PHPUnit_Framework_MockObject_MockObject $mockContentBlockSource */
        $mockContentBlockSource = $this->getMock(ContentBlockSource::class, [], [], '', false);
        $mockContentBlockSource->method('getContent')->willReturn($dummyContentBlockContent);
        $mockContentBlockSource->method('getContextData')->willReturn([]);

        $stubKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubKeyGenerator->method('getKeyForContext')->willReturn($stubSnippetKey);

        $this->stubSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')
            ->willReturn($stubKeyGenerator);
        $this->mockContextBuilder->method('createContext')->willReturn($stubContext);

        $expectedSnippet = Snippet::create($stubSnippetKey, $dummyContentBlockContent);

        $this->mockSnippetList->expects($this->once())->method('add')->with($expectedSnippet);

        $this->renderer->render($mockContentBlockSource);
    }
}
