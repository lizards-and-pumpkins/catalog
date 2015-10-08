<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;

/**
 * @covers \LizardsAndPumpkins\Product\ProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\SnippetList
 * @uses   \LizardsAndPumpkins\Snippet
 */
class ProductJsonSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductJsonSnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProduct;

    /**
     * @param \Iterator $iterator
     * @param int $indexToGet
     * @return mixed
     */
    private function getItemByIndexFromIterator(\Iterator $iterator, $indexToGet)
    {
        for ($i = 0; $i < $indexToGet; $i++) {
            $iterator->next();
        }
        return $iterator->current();
    }

    /**
     * @param SnippetList $snippetList
     * @param int $number
     * @return Snippet
     */
    private function getSnippetNumber(SnippetList $snippetList, $number)
    {
        return $this->getItemByIndexFromIterator($snippetList->getIterator(), $number);
    }

    /**
     * @param mixed $expectedContent
     * @param SnippetList $snippetList
     * @param int $snippetNumber
     */
    private function assertSnippetNumberContent($expectedContent, SnippetList $snippetList, $snippetNumber)
    {
        $snippet = $this->getSnippetNumber($snippetList, $snippetNumber);
        $this->assertSame($expectedContent, $snippet->getContent());
    }

    protected function setUp()
    {
        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubProductJsonKeyGenerator */
        $stubProductJsonKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubProductJsonKeyGenerator->method('getKeyForContext')->willReturn('test-key');
        $this->snippetRenderer = new ProductJsonSnippetRenderer($stubProductJsonKeyGenerator);
        
        $this->stubProduct = $this->getMock(Product::class);
        $this->stubProduct->method('getContext')->willReturn($this->getMock(Context::class));
    }

    public function testItReturnsTheJsonSerializedProduct()
    {
        $expectedJsonContent = ['product_id' => 'test-dummy'];
        
        $this->stubProduct->method('jsonSerialize')->willReturn($expectedJsonContent);
        
        $snippetList = $this->snippetRenderer->render($this->stubProduct);
        
        $this->assertInstanceOf(SnippetList::class, $snippetList);
        $this->assertCount(1, $snippetList);
        $this->assertSnippetNumberContent(json_encode($expectedJsonContent), $snippetList, 0);
    }
}
