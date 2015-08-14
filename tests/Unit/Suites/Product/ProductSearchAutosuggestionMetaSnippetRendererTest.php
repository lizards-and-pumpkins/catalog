<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\Renderer\BlockRenderer;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

/**
 * @covers \Brera\Product\ProductSearchAutosuggestionMetaSnippetRenderer
 */
class ProductSearchAutosuggestionMetaSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dummySnippetKey = 'foo';

    /**
     * @var string
     */
    private $dummyRootSnippetCode = 'bar';

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var ProductSearchAutosuggestionMetaSnippetRenderer
     */
    private $renderer;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextSource;

    protected function setUp()
    {
        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummySnippetKey);

        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        $stubBlockRenderer->method('getRootSnippetCode')->willReturn($this->dummyRootSnippetCode);
        $stubBlockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        $this->mockSnippetList = $this->getMock(SnippetList::class);

        $this->renderer = new ProductSearchAutosuggestionMetaSnippetRenderer(
            $this->mockSnippetList,
            $stubSnippetKeyGenerator,
            $stubBlockRenderer
        );

        $stubContext = $this->getMock(Context::class);

        $this->stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->stubContextSource->method('getAllAvailableContexts')->willReturn([$stubContext]);
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testSnippetListIsReturned()
    {
        $result = $this->renderer->render('dummy-data-object', $this->stubContextSource);
        $this->assertInstanceOf(SnippetList::class, $result);
    }

    public function testSnippetWithValidJsonAsContentAddedToList()
    {
        $expectedSnippetContent = [
            ProductSearchAutosuggestionMetaSnippetContent::KEY_ROOT_SNIPPET_CODE  => $this->dummyRootSnippetCode,
            ProductSearchAutosuggestionMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [$this->dummyRootSnippetCode]
        ];
        $expectedSnippet = Snippet::create($this->dummySnippetKey, json_encode($expectedSnippetContent));
        $this->mockSnippetList->expects($this->once())->method('add')->with($expectedSnippet);

        $this->renderer->render('dummy-data-object', $this->stubContextSource);
    }
}
