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
     * @var ProductSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductSource;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContextSource;

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

        $this->mockContextSource = $this->getMockBuilder(ContextSource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getContextMatrix', 'getAllAvailableContexts'])
            ->getMock();

        $this->mockProductSource = $this->getMock(ProductSource::class, [], [], '', false);
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testEmptySnippetListIsReturned()
    {
        $this->mockContextSource->expects($this->any())
            ->method('getAllAvailableContexts')
            ->willReturn([]);

        $result = $this->renderer->render($this->mockProductSource, $this->mockContextSource);

        $this->assertInstanceOf(SnippetList::class, $result);
        $this->assertEmpty($result);
    }

    public function testSnippetListContainingSnippetWithGivenKeyAndPriceIsReturned()
    {
        $stubContext = $this->getMock(Context::class);
        $dummyPriceSnippetKey = 'bar';
        $dummyPriceAttributeValue = '1';

        $mockProduct = $this->getMock(Product::class, [], [], '', false);
        $mockProduct->expects($this->any())
            ->method('getAttributeValue')
            ->with($this->dummyPriceAttributeCode)
            ->willReturn($dummyPriceAttributeValue);

        $this->mockProductSource->expects($this->any())
            ->method('getProductForContext')
            ->willReturn($mockProduct);

        $this->mockContextSource->expects($this->any())
            ->method('getAllAvailableContexts')
            ->willReturn([$stubContext]);

        $this->mockSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturn($dummyPriceSnippetKey);

        $dummyPrice = Price::fromString($dummyPriceAttributeValue);
        $expectedSnippet = Snippet::create($dummyPriceSnippetKey, $dummyPrice->getAmount());

        $this->mockSnippetList->expects($this->once())
            ->method('add')
            ->with($expectedSnippet);

        $this->renderer->render($this->mockProductSource, $this->mockContextSource);
    }
}
