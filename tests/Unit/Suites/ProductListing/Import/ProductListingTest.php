<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKey;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListing
 */
class ProductListingTest extends TestCase
{
    /**
     * @var UrlKey|MockObject
     */
    private $stubUrlKey;

    /**
     * @var string
     */
    private $dummyContextData = ['foo' => 'bar', 'baz' => 'qux'];

    /**
     * @var SearchCriteria|MockObject
     */
    private $stubCriteria;

    /**
     * @var ProductListingAttributeList|MockObject
     */
    private $stubProductListingAttributeList;

    /**
     * @var ProductListing
     */
    private $productListing;

    final protected function setUp(): void
    {
        $this->stubUrlKey = $this->createMock(UrlKey::class);
        $this->stubCriteria = $this->createMock(SearchCriteria::class);
        $this->stubProductListingAttributeList = $this->createMock(ProductListingAttributeList::class);

        $this->productListing = new ProductListing(
            $this->stubUrlKey,
            $this->dummyContextData,
            $this->stubProductListingAttributeList,
            $this->stubCriteria
        );
    }

    public function testProductListingUrlKeyIsReturned(): void
    {
        $this->assertSame($this->stubUrlKey, $this->productListing->getUrlKey());
    }

    public function testProductListingContextDataIsReturned(): void
    {
        $this->assertSame($this->dummyContextData, $this->productListing->getContextData());
    }

    public function testProductListingIsReturned(): void
    {
        $this->assertSame($this->stubCriteria, $this->productListing->getCriteria());
    }

    public function testReturnsProductListingAttributesList(): void
    {
        $this->assertSame($this->stubProductListingAttributeList, $this->productListing->getAttributesList());
    }

    public function testCanBeSerializedAndRehydrated(): void
    {
        $rehydrated = ProductListing::rehydrate($this->productListing->serialize());
        $this->assertEquals($rehydrated, $this->productListing);
    }
}
