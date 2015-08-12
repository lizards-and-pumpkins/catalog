<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

/**
 * @covers \Brera\Product\ProductInSearchAutocompletionInContextSnippetRenderer
 * @uses   \Brera\Snippet
 */
class ProductInSearchAutocompletionInContextSnippetRendererTest extends \PHPUnit_Framework_TestCase
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
     * @var ProductInSearchAutocompletionInContextSnippetRenderer
     */
    private $renderer;

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);
        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        /**
         * @var ProductInSearchAutocompletionBlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer
         */
        $stubBlockRenderer = $this->getMock(ProductInSearchAutocompletionBlockRenderer::class, [], [], '', false);
        $stubBlockRenderer->method('render')->willReturn('dummy content');

        $this->renderer = new ProductInSearchAutocompletionInContextSnippetRenderer(
            $this->mockSnippetList,
            $stubBlockRenderer,
            $this->mockSnippetKeyGenerator
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testProductInAutocompletionInContextSnippetsAreAddedToSnippetList()
    {
        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn('stub-content-key');

        $this->mockSnippetList->expects($this->once())->method('add');

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class, [], [], '', false);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class, [], [], '', false);

        $this->renderer->render($stubProduct, $stubContext);
    }

    public function testProductIdIsPassedToKeyGenerator()
    {
        $dummyProductId = 'foo';

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->method('getId')->willReturn($dummyProductId);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);

        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn('stub-content-key');
        $this->mockSnippetKeyGenerator->expects($this->once())->method('getKeyForContext')
            ->with($this->anything(), ['product_id' => $dummyProductId]);
        $this->renderer->render($stubProduct, $stubContext);
    }
}
