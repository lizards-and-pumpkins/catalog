<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetKeyGenerator;
use Brera\SnippetResultList;

/**
 * @covers \Brera\Product\ProductInListingInContextSnippetRenderer
 * @uses   \Brera\SnippetResult
 */
class ProductInListingInContextSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductInListingInContextSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetResultList;

    /**
     * @var ProductInListingBlockRenderer||\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductInListingBlockRenderer;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

    protected function setUp()
    {
        $this->mockSnippetResultList = $this->getMock(SnippetResultList::class);

        $this->stubProductInListingBlockRenderer = $this->getMockBuilder(ProductInListingBlockRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubProductInListingBlockRenderer->expects($this->any())
            ->method('render')
            ->willReturn('dummy content');
        $this->stubProductInListingBlockRenderer->expects($this->any())
            ->method('getRootSnippetCode')
            ->willReturn('dummy root block code');
        $this->stubProductInListingBlockRenderer->expects($this->any())
            ->method('getNestedSnippetCodes')
            ->willReturn([]);

        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->mockSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturn('stub-content-key');

        $this->renderer = new ProductInListingInContextSnippetRenderer(
            $this->mockSnippetResultList,
            $this->stubProductInListingBlockRenderer,
            $this->mockSnippetKeyGenerator
        );
    }

    /**
     * @test
     */
    public function itShouldRenderProductInListingViewSnippets()
    {
        $this->mockSnippetResultList->expects($this->once())
            ->method('add');

        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->expects($this->any())
            ->method('getId')
            ->willReturn(2);

        $stubContext = $this->getMock(Context::class, [], [], '', false);

        $this->renderer->render($stubProduct, $stubContext);
    }

    /**
     * @test
     */
    public function itShouldDelegateToTheKeyGeneratorToFetchTheContextParts()
    {
        $testContextParts = ['version', 'website', 'language'];
        $this->mockSnippetKeyGenerator->expects($this->once())->method('getContextPartsUsedForKey')
            ->willReturn($testContextParts);

        $this->assertSame($testContextParts, $this->renderer->getUsedContextParts());
    }
}
