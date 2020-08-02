<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\UrlKey;

use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector
 * @uses   \LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollection
 * @uses   \LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContext
 * @uses   \LizardsAndPumpkins\Import\Product\UrlKey\UrlKey
 */
class UrlKeyForContextCollectorTest extends TestCase
{
    /**
     * @var UrlKeyForContextCollector
     */
    private $urlKeyCollector;

    /**
     * @var ContextSource|MockObject
     */
    private $stubContextSource;
    
    private $testContextData = ['foo' => 'bar'];

    /**
     * @param string $urlKey
     * @return Product|MockObject
     */
    private function createStubProductWithUrlKey(string $urlKey) : Product
    {
        $stubProduct = $this->createMock(Product::class);
        $stubProduct->method('getFirstValueOfAttribute')->with(Product::URL_KEY)->willReturn($urlKey);
        $stubProduct->method('getContext')->willReturn($this->createMock(Context::class));
        return $stubProduct;
    }

    final protected function setUp(): void
    {
        $this->stubContextSource = $this->createMock(ContextSource::class);
        $this->stubContextSource->method('getContextsForParts')
            ->with(array_keys($this->testContextData))
            ->willReturn([$this->createMock(Context::class)]);
        
        $this->urlKeyCollector = new UrlKeyForContextCollector($this->stubContextSource);
    }

    public function testItReturnsAUrlKeyCollectionForProducts(): void
    {
        /** @var Product|MockObject $stubProduct */
        $stubProduct = $this->createStubProductWithUrlKey('product.html');
        $collection = $this->urlKeyCollector->collectProductUrlKeys($stubProduct);
        $this->assertInstanceOf(UrlKeyForContextCollection::class, $collection);
        $this->assertCount(1, $collection);
    }

    public function testItReturnsAUrlKeyCollectionForListings(): void
    {
        /** @var ProductListing|MockObject $stubListingCriteria */
        $stubListingCriteria = $this->createMock(ProductListing::class);
        $stubListingCriteria->method('getContextData')->willReturn($this->testContextData);
        $stubListingCriteria->expects($this->once())->method('getUrlKey')
            ->willReturn(UrlKey::fromString('listing.html'));
        $collection = $this->urlKeyCollector->collectListingUrlKeys($stubListingCriteria);
        $this->assertInstanceOf(UrlKeyForContextCollection::class, $collection);
        $this->assertCount(1, $collection);
    }
}
