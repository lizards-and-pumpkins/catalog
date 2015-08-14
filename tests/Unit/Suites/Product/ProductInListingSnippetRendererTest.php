<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\InvalidProjectionSourceDataTypeException;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

/**
 * @covers \Brera\Product\ProductInListingSnippetRenderer
 * @uses   \Brera\Snippet
 */
class ProductInListingSnippetRendererTest extends \PHPUnit_Framework_TestCase
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
     * @var ProductInListingSnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextSource;

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);

        /** @var ProductInListingBlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->getMock(ProductInListingBlockRenderer::class, [], [], '', false);
        $stubBlockRenderer->method('render')->willReturn('dummy content');

        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn('stub-content-key');

        $this->snippetRenderer = new ProductInListingSnippetRenderer(
            $this->mockSnippetList,
            $stubBlockRenderer,
            $this->mockSnippetKeyGenerator
        );

        $stubContext = $this->getMock(Context::class);

        $this->stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->stubContextSource->method('getAllAvailableContexts')->willReturn([$stubContext]);
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotAProductSource()
    {
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);
        $this->snippetRenderer->render('invalid-projection-source-data', $this->stubContextSource);
    }

    public function testProductInListingViewSnippetsAreRendered()
    {
        $dummyProductId = 'foo';
        $stubProductSource = $this->getStubProductSource($dummyProductId);

        $this->mockSnippetList->expects($this->once())->method('add');

        $this->snippetRenderer->render($stubProductSource, $this->stubContextSource);
    }

    public function testProductIdIsPassedToKeyGenerator()
    {
        $dummyProductId = 'foo';
        $stubProductSource = $this->getStubProductSource($dummyProductId);

        $this->mockSnippetKeyGenerator->expects($this->once())->method('getKeyForContext')
            ->with($this->anything(), ['product_id' => $dummyProductId]);

        $this->snippetRenderer->render($stubProductSource, $this->stubContextSource);
    }

    /**
     * @param string $dummyProductIdString
     * @return ProductSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubProductSource($dummyProductIdString)
    {
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $stubProductId->method('__toString')->willReturn($dummyProductIdString);

        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->method('getId')->willReturn($stubProductId);

        $stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);
        $stubProductSource->method('getId')->willReturn($stubProductId);
        $stubProductSource->method('getProductForContext')->willReturn($stubProduct);

        return $stubProductSource;
    }
}
