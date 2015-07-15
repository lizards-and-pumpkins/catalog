<?php

namespace Brera\Content;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

/**
 * @covers \Brera\Content\ContentBlockSnippetRenderer
 * @uses   \Brera\Snippet
 */
class ContentBlockSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

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
        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->mockContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);

        $this->renderer = new ContentBlockSnippetRenderer(
            $this->mockSnippetList,
            $this->mockSnippetKeyGenerator,
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

        $mockContentBlockSource = $this->getMock(ContentBlockSource::class, [], [], '', false);
        $mockContentBlockSource->method('getContent')->willReturn($dummyContentBlockContent);
        $mockContentBlockSource->method('getContextData')->willReturn([]);

        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn($stubSnippetKey);
        $this->mockContextBuilder->method('getContext')->willReturn($stubContext);

        $expectedSnippet = Snippet::create($stubSnippetKey, $dummyContentBlockContent);

        $this->mockSnippetList->expects($this->once())->method('add')->with($expectedSnippet);

        $this->renderer->render($mockContentBlockSource);
    }
}
