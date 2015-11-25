<?php


namespace LizardsAndPumpkins\Projection;

use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\UrlKey;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Product\ProductListingCriteria;

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
    
    private $testContextData = ['foo' => 'bar'];

    /**
     * @param string $urlKey
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductWithUrlKey($urlKey)
    {
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('getFirstValueOfAttribute')->with(Product::URL_KEY)->willReturn($urlKey);
        $stubProduct->method('getContext')->willReturn($this->getMock(Context::class));
        return $stubProduct;
    }

    protected function setUp()
    {
        $this->stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->stubContextSource->method('getContextsForParts')
            ->with(array_keys($this->testContextData))
            ->willReturn([$this->getMock(Context::class)]);
        
        $this->urlKeyCollector = new UrlKeyForContextCollector($this->stubContextSource);
    }

    public function testItReturnsAUrlKeyCollectionForProducts()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createStubProductWithUrlKey('product.html');
        $collection = $this->urlKeyCollector->collectProductUrlKeys($stubProduct);
        $this->assertInstanceOf(UrlKeyForContextCollection::class, $collection);
        $this->assertCount(1, $collection);
    }

    public function testItReturnsAUrlKeyCollectionForListings()
    {
        /** @var ProductListingCriteria|\PHPUnit_Framework_MockObject_MockObject $stubListingCriteria */
        $stubListingCriteria = $this->getMock(ProductListingCriteria::class, [], [], '', false);
        $stubListingCriteria->method('getContextData')->willReturn($this->testContextData);
        $stubListingCriteria->expects($this->once())->method('getUrlKey')
            ->willReturn(UrlKey::fromString('listing.html'));
        $collection = $this->urlKeyCollector->collectListingUrlKeys($stubListingCriteria);
        $this->assertInstanceOf(UrlKeyForContextCollection::class, $collection);
        $this->assertCount(1, $collection);
    }
}
