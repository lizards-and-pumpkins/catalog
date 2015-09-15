<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
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
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextSource;

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

    public function testProductInAutosuggestionInContextSnippetIsRendered()
    {
        $dummyProductId = 'foo';
        $stubProductSource = $this->getStubProductSource($dummyProductId);

        $result = $this->snippetRenderer->render($stubProductSource, $this->stubContextSource);

        $this->assertInstanceOf(SnippetList::class, $result);
        $this->assertCount(1, $result);
        $this->assertContainsOnly(Snippet::class, $result);
    }

    public function testProductIdIsPassedToKeyGenerator()
    {
        $dummyProductId = 'foo';
        $stubProductSource = $this->getStubProductSource($dummyProductId);

        $this->mockSnippetKeyGenerator->expects($this->once())->method('getKeyForContext')
            ->with($this->anything(), [Product::ID => $dummyProductId]);

        $this->snippetRenderer->render($stubProductSource, $this->stubContextSource);
    }
}
