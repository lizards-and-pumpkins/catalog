<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

/**
 * @covers \Brera\Product\ProductStockQuantitySnippetRenderer
 * @uses   \Brera\Product\ProductId
 * @uses   \Brera\Product\ProductStockQuantity
 * @uses   \Brera\SnippetList
 * @uses   \Brera\Snippet
 */
class ProductStockQuantitySnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductStockQuantitySnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContextBuilder;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    protected function setUp()
    {
        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->mockContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
        $this->mockSnippetList = $this->getMock(SnippetList::class);

        $this->renderer = new ProductStockQuantitySnippetRenderer(
            $this->mockSnippetKeyGenerator,
            $this->mockContextBuilder,
            $this->mockSnippetList
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testSnippetListContainingSnippetWithGivenKeyAndStockIsReturned()
    {
        $stubSnippetKey = 'bar';
        $stubQuantity = '1';

        $stubSku = $this->getMock(Sku::class);
        $stubContext = $this->getMock(Context::class);

        $mockStock = $this->getMock(ProductStockQuantity::class, [], [], '', false);
        $mockStock->expects($this->any())
            ->method('getQuantity')
            ->willReturn($stubQuantity);

        $mockProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);
        $mockProductStockQuantitySource->expects($this->any())
            ->method('getSku')
            ->willReturn($stubSku);
        $mockProductStockQuantitySource->expects($this->any())
            ->method('getContextData')
            ->willReturn([]);
        $mockProductStockQuantitySource->expects($this->any())
            ->method('getStock')
            ->willReturn($mockStock);

        $this->mockContextBuilder->expects($this->any())
            ->method('getContext')
            ->willReturn($stubContext);

        $this->mockSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturn($stubSnippetKey);

        $dummyStock = ProductStockQuantity::fromString($stubQuantity);
        $expectedSnippet = Snippet::create($stubSnippetKey, $dummyStock->getQuantity());

        $this->mockSnippetList->expects($this->once())
            ->method('add')
            ->with($expectedSnippet);

        $this->renderer->render($mockProductStockQuantitySource);
    }
}
