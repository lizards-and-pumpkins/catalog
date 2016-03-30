<?php

namespace LizardsAndPumpkins\ProductSearch;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;

use LizardsAndPumpkins\ProductSearch\TemplateRendering\ProductInSearchAutosuggestionBlockRenderer;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ProductInSearchAutosuggestionSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
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

    protected function setUp()
    {
        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn('stub-content-key');

        $this->stubBlockRenderer = $this->getMock(ProductInSearchAutosuggestionBlockRenderer::class, [], [], '', false);
        $this->stubBlockRenderer->method('render')->willReturn('dummy content');

        $this->snippetRenderer = new ProductInSearchAutosuggestionSnippetRenderer(
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
        $this->expectException(InvalidProjectionSourceDataTypeException::class);
        $this->snippetRenderer->render('invalid-projection-source-data');
    }

    public function testProductInAutosuggestionInContextSnippetIsRendered()
    {
        $dummyProductId = 'foo';
        $stubProductView = $this->getStubProductView($dummyProductId);

        $result = $this->snippetRenderer->render($stubProductView);

        $this->assertCount(1, $result);
        $this->assertContainsOnly(Snippet::class, $result);
    }

    public function testProductIdIsPassedToKeyGenerator()
    {
        $dummyProductId = 'foo';
        $stubProduct = $this->getStubProductView($dummyProductId);

        $this->mockSnippetKeyGenerator->expects($this->once())->method('getKeyForContext')
            ->with($this->anything(), [Product::ID => $stubProduct->getId()])
            ->willReturn('stub-content-key');

        $this->snippetRenderer->render($stubProduct);
    }
}
