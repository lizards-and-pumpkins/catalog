<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductInListingSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 * @uses   \LizardsAndPumpkins\SnippetList
 */
class ProductInListingSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

    /**
     * @var ProductInListingSnippetRenderer
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

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(SimpleProduct::class, [], [], '', false);
        $stubProduct->method('getId')->willReturn($stubProductId);
        
        $stubProduct->method('getContext')->willReturn($this->getMock(Context::class));
        
        return $stubProduct;
    }

    protected function setUp()
    {
        $testSnippetList = new SnippetList;

        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn('stub-content-key');

        $this->snippetRenderer = new ProductInListingSnippetRenderer($testSnippetList, $this->mockSnippetKeyGenerator);
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

    public function testProductInListingViewSnippetIsRendered()
    {
        $dummyProductId = 'foo';
        $stubProduct = $this->getStubProduct($dummyProductId);

        $result = $this->snippetRenderer->render($stubProduct);

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
