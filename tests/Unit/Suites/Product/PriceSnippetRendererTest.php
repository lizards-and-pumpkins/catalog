<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\SnippetKeyGenerator;
use Brera\SnippetRenderer;
use Brera\Snippet;
use Brera\SnippetList;

/**
 * @covers Brera\Product\PriceSnippetRenderer
 * @uses   Brera\Product\Price
 * @uses   Brera\Snippet
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
        $mockProductSource = $this->getMock(ProductSource::class, [], [], '', false);

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
        $mockProduct->method('getFirstAttributeValue')
            ->with($this->dummyPriceAttributeCode)
            ->willReturn($dummyPriceAttributeValue);

        $mockProductSource = $this->getMock(ProductSource::class, [], [], '', false);
        $mockProductSource->method('getProductForContext')
            ->willReturn($mockProduct);

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
