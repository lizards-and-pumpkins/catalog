<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
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
     * @param string $dummyProductIdString
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubProduct($dummyProductIdString)
    {
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $stubProductId->method('__toString')->willReturn($dummyProductIdString);
        
        $stubContext = $this->getMock(Context::class);

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(SimpleProduct::class, [], [], '', false);
        $stubProduct->method('getId')->willReturn($stubProductId);
        $stubProduct->method('getContext')->willReturn($stubContext);

        return $stubProduct;
    }

    protected function setUp()
    {
        $testSnippetList = new SnippetList;

        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn('stub-content-key');

        /**
         * @var ProductInSearchAutosuggestionBlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer
         */
        $stubBlockRenderer = $this->getMock(ProductInSearchAutosuggestionBlockRenderer::class, [], [], '', false);
        $stubBlockRenderer->method('render')->willReturn('dummy content');

        $this->snippetRenderer = new ProductInSearchAutosuggestionSnippetRenderer(
            $testSnippetList,
            $stubBlockRenderer,
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
        $stubProductBuilder = $this->getStubProduct($dummyProductId);

        $result = $this->snippetRenderer->render($stubProductBuilder);

        $this->assertInstanceOf(SnippetList::class, $result);
        $this->assertCount(1, $result);
        $this->assertContainsOnly(Snippet::class, $result);
    }

    public function testProductIdIsPassedToKeyGenerator()
    {
        $dummyProductId = 'foo';
        $stubProductBuilder = $this->getStubProduct($dummyProductId);

        $this->mockSnippetKeyGenerator->expects($this->once())->method('getKeyForContext')
            ->with($this->anything(), [SimpleProduct::ID => $dummyProductId]);

        $this->snippetRenderer->render($stubProductBuilder);
    }
}
