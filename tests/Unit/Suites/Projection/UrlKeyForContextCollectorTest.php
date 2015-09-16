<?php


namespace LizardsAndPumpkins\Projection;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Product\ProductSource;

/**
 * @covers \LizardsAndPumpkins\Projection\UrlKeyForContextCollector
 * @uses   \LizardsAndPumpkins\Projection\UrlKeyForContextCollection
 * @uses   \LizardsAndPumpkins\Projection\UrlKeyForContext
 * @uses   \LizardsAndPumpkins\UrlKey
 */
class UrlKeyForContextCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlKeyForContextCollector
     */
    private $urlKeyCollector;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextSource;

    /**
     * @var ProductSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductSource;

    /**
     * @param string $urlKey
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductWithUrlKey($urlKey)
    {
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->method('getFirstValueOfAttribute')->with(Product::URL_KEY)->willReturn($urlKey);
        return $stubProduct;
    }

    protected function setUp()
    {
        $this->stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);

        $this->urlKeyCollector = new UrlKeyForContextCollector();
    }

    public function testItReturnsAUrlKeyCollection()
    {
        $this->stubContextSource->method('getAllAvailableContexts')->willReturn([$this->getMock(Context::class)]);
        $this->stubProductSource->method('getProductForContext')->willReturn(
            $this->createStubProductWithUrlKey('product.html')
        );
        $collection = $this->urlKeyCollector->collectProductUrlKeys($this->stubProductSource, $this->stubContextSource);
        $this->assertInstanceOf(UrlKeyForContextCollection::class, $collection);
        $this->assertCount(1, $collection);
    }
}
