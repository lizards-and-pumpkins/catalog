<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
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
    private $dummyBackOrderAvailabilityAttributeCode = 'foo';

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class, [], [], '', false);
        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        $this->renderer = new ProductBackOrderAvailabilitySnippetRenderer(
            $this->mockSnippetList,
            $this->mockSnippetKeyGenerator,
            $this->dummyBackOrderAvailabilityAttributeCode
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testSnippetListContainingSnippetWithGivenKeyAndBackOrderAvailabilityIsReturned()
    {
        $stubContext = $this->getMock(Context::class);
        $dummyBackOrderAvailabilitySnippetKey = 'bar';
        $dummyBackOrderAvailabilityAttributeValue = '1';

        $mockProduct = $this->getMock(Product::class);
        $mockProduct->method('getFirstValueOfAttribute')
            ->with($this->dummyBackOrderAvailabilityAttributeCode)
            ->willReturn($dummyBackOrderAvailabilityAttributeValue);
        $mockProduct->method('getContext')->willReturn($stubContext);
        
        $this->mockSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($dummyBackOrderAvailabilitySnippetKey);

        $expectedSnippet = Snippet::create(
            $dummyBackOrderAvailabilitySnippetKey,
            $dummyBackOrderAvailabilityAttributeValue
        );

        $this->mockSnippetList->expects($this->once())
            ->method('add')
            ->with($expectedSnippet);

        $this->renderer->render($mockProduct);
    }
}
