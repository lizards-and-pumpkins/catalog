<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductInSearchAutosuggestionSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 * @uses   \LizardsAndPumpkins\SnippetList
 */
class ProductInSearchAutosuggestionSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

    /**
     * @var ProductInSearchAutosuggestionSnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var ProductInSearchAutosuggestionBlockRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubBlockRenderer;

    /**
     * @param string $dummyProductIdString
     * @return ProductView|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubProductView($dummyProductIdString)
    {
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $stubProductId->method('__toString')->willReturn($dummyProductIdString);
        
        $stubContext = $this->getMock(Context::class);

        $stubProduct = $this->getMock(ProductView::class);
        $stubProduct->method('getId')->willReturn($stubProductId);
        $stubProduct->method('getContext')->willReturn($stubContext);

        return $stubProduct;
    }

    /**
     * @param SnippetList $testSnippetList
     * @param ProductInSearchAutosuggestionBlockRenderer $stubBlockRenderer
     * @param SnippetKeyGenerator $snippetKeyGenerator
     * @return ProductInSearchAutosuggestionSnippetRenderer
     */
    private function createInstanceUnderTest(
        SnippetList $testSnippetList,
        ProductInSearchAutosuggestionBlockRenderer $stubBlockRenderer,
        SnippetKeyGenerator $snippetKeyGenerator
    ) {
        return new ProductInSearchAutosuggestionSnippetRenderer(
            $testSnippetList,
            $stubBlockRenderer,
            $snippetKeyGenerator
        );
    }

    protected function setUp()
    {
        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn('stub-content-key');

        $this->stubBlockRenderer = $this->getMock(ProductInSearchAutosuggestionBlockRenderer::class, [], [], '', false);
        $this->stubBlockRenderer->method('render')->willReturn('dummy content');

        $this->snippetRenderer = $this->createInstanceUnderTest(
            new SnippetList(),
            $this->stubBlockRenderer,
            $this->mockSnippetKeyGenerator
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotAProductBuilder()
    {
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);
        $this->snippetRenderer->render('invalid-projection-source-data');
    }

    public function testProductInAutosuggestionInContextSnippetIsRendered()
    {
        $dummyProductId = 'foo';
        $stubProductView = $this->getStubProductView($dummyProductId);

        $result = $this->snippetRenderer->render($stubProductView);

        $this->assertInstanceOf(SnippetList::class, $result);
        $this->assertCount(1, $result);
        $this->assertContainsOnly(Snippet::class, $result);
    }

    public function testProductIdIsPassedToKeyGenerator()
    {
        $dummyProductId = 'foo';
        $stubProduct = $this->getStubProductView($dummyProductId);

        $mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $mockSnippetKeyGenerator->expects($this->once())->method('getKeyForContext')
            ->with($this->anything(), [Product::ID => $stubProduct->getId()])
            ->willReturn('stub-content-key');
        
        $snippetRenderer = $this->createInstanceUnderTest(
            new SnippetList,
            $this->stubBlockRenderer,
            $mockSnippetKeyGenerator
        );

        $snippetRenderer->render($stubProduct);
    }
}
