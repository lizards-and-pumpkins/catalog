<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projection\Catalog\InternalToPublicProductJsonData;
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
     * @var InternalToPublicProductJsonData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockInternalToPublicProductJson;

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
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('getId')->willReturn($stubProductId);
        $stubProduct->method('getContext')->willReturn($this->getMock(Context::class));
        $stubProduct->method('jsonSerialize')->willReturn(['product_id' => $stubProductId]);
        
        return $stubProduct;
    }

    /**
     * @param SnippetKeyGenerator $snippetKeyGenerator
     * @param InternalToPublicProductJsonData $internalToPublicProductJsonData
     * @return ProductInListingSnippetRenderer
     */
    private function createInstanceUnderTest($snippetKeyGenerator, $internalToPublicProductJsonData)
    {
        return new ProductInListingSnippetRenderer(
            $snippetKeyGenerator,
            $internalToPublicProductJsonData
        );
    }

    protected function setUp()
    {
        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn('stub-content-key');

        $this->mockInternalToPublicProductJson = $this->getMock(InternalToPublicProductJsonData::class);

        $this->snippetRenderer = $this->createInstanceUnderTest(
            $this->mockSnippetKeyGenerator,
            $this->mockInternalToPublicProductJson
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

    public function testProductInListingViewSnippetIsRendered()
    {
        $this->mockInternalToPublicProductJson->expects($this->atLeastOnce())
            ->method('transformProduct')->willReturnArgument(0);
        
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
        $stubProduct = $this->getStubProduct($dummyProductId);

        $mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $mockSnippetKeyGenerator->expects($this->once())->method('getKeyForContext')
            ->with($this->anything(), [Product::ID => $stubProduct->getId()])
            ->willReturn('stub-content-key');

        $snippetRenderer = $this->createInstanceUnderTest(
            $mockSnippetKeyGenerator,
            $this->mockInternalToPublicProductJson
        );

        $snippetRenderer->render($stubProduct);
    }
}
