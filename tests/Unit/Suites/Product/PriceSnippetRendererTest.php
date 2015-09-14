<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetList;

/**
 * @covers \LizardsAndPumpkins\Product\PriceSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\Price
 * @uses   \LizardsAndPumpkins\Snippet
 */
class PriceSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PriceSnippetRenderer
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
    private $dummyPriceAttributeCode = 'foo';

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);
        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        $this->renderer = new PriceSnippetRenderer(
            $this->mockSnippetList,
            $this->mockSnippetKeyGenerator,
            $this->dummyPriceAttributeCode
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

    public function testSnippetListContainingSnippetWithGivenKeyAndPriceIsReturned()
    {
        $stubContext = $this->getMock(Context::class);
        $dummyPriceSnippetKey = 'bar';
        $dummyPriceAttributeValue = '1';

        $mockProduct = $this->getMock(Product::class, [], [], '', false);
        $mockProduct->method('getFirstValueOfAttribute')
            ->with($this->dummyPriceAttributeCode)
            ->willReturn($dummyPriceAttributeValue);

        /** @var ProductSource|\PHPUnit_Framework_MockObject_MockObject $mockProductSource */
        $mockProductSource = $this->getMock(ProductSource::class, [], [], '', false);
        $mockProductSource->method('getProductForContext')
            ->willReturn($mockProduct);

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $mockContextSource */
        $mockContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $mockContextSource->method('getAllAvailableContexts')
            ->willReturn([$stubContext]);

        $this->mockSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($dummyPriceSnippetKey);

        $dummyPrice = Price::fromString($dummyPriceAttributeValue);
        $expectedSnippet = Snippet::create($dummyPriceSnippetKey, $dummyPrice->getAmount());

        $this->mockSnippetList->expects($this->once())
            ->method('add')
            ->with($expectedSnippet);

        $this->renderer->render($mockProductSource, $mockContextSource);
    }
}
