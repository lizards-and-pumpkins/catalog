<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductBackOrderAvailabilitySnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 */
class ProductBackOrderAvailabilitySnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductBackOrderAvailabilitySnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

    /**
     * @var string
     */
    private $dummyBackOrderAvialabilityAttributeCode = 'foo';

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);
        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        $this->renderer = new ProductBackOrderAvailabilitySnippetRenderer(
            $this->mockSnippetList,
            $this->mockSnippetKeyGenerator,
            $this->dummyBackOrderAvialabilityAttributeCode
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testEmptySnippetListIsReturned()
    {
        /** @var ProductSource|\PHPUnit_Framework_MockObject_MockObject $mockProductSource */
        $mockProductSource = $this->getMock(ProductSource::class, [], [], '', false);

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $mockContextSource */
        $mockContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $mockContextSource->method('getAllAvailableContexts')
            ->willReturn([]);

        $result = $this->renderer->render($mockProductSource, $mockContextSource);

        $this->assertInstanceOf(SnippetList::class, $result);
        $this->assertEmpty($result);
    }

    public function testSnippetListContainingSnippetWithGivenKeyAndBackOrderAvailabilityIsReturned()
    {
        $stubContext = $this->getMock(Context::class);
        $dummyBackOrderAvailabilitySnippetKey = 'bar';
        $dummyBackOrderAvailabilityAttributeValue = '1';

        $mockProduct = $this->getMock(Product::class, [], [], '', false);
        $mockProduct->method('getFirstValueOfAttribute')
            ->with($this->dummyBackOrderAvialabilityAttributeCode)
            ->willReturn($dummyBackOrderAvailabilityAttributeValue);

        /** @var ProductSource|\PHPUnit_Framework_MockObject_MockObject $mockProductSource */
        $mockProductSource = $this->getMock(ProductSource::class, [], [], '', false);
        $mockProductSource->method('getProductForContext')
            ->willReturn($mockProduct);

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $mockContextSource */
        $mockContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $mockContextSource->method('getAllAvailableContexts')
            ->willReturn([$stubContext]);

        $this->mockSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($dummyBackOrderAvailabilitySnippetKey);

        $expectedSnippet = Snippet::create(
            $dummyBackOrderAvailabilitySnippetKey,
            $dummyBackOrderAvailabilityAttributeValue
        );

        $this->mockSnippetList->expects($this->once())
            ->method('add')
            ->with($expectedSnippet);

        $this->renderer->render($mockProductSource, $mockContextSource);
    }
}
