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
     * @var UrlKey|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubUrlKey;

    /**
     * @var string
     */
    private $dummyContextData = ['foo' => 'bar', 'baz' => 'qux'];

    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubCriteria;

    /**
     * @var ProductListingAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingAttributeList;

    /**
     * @var ProductListing
     */
    private $productListing;

    final protected function setUp()
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

    public function testProductListingUrlKeyIsReturned()
    {
        $this->assertSame($this->stubUrlKey, $this->productListing->getUrlKey());
    }

    public function testProductListingContextDataIsReturned()
    {
        $this->assertSame($this->dummyContextData, $this->productListing->getContextData());
    }

    public function testProductListingIsReturned()
    {
        $this->assertSame($this->stubCriteria, $this->productListing->getCriteria());
    }

    public function testReturnsProductListingAttributesList()
    {
        $this->assertSame($this->stubProductListingAttributeList, $this->productListing->getAttributesList());
    }

    public function testCanBeSerializedAndRehydrated()
    {
        $rehydrated = ProductListing::rehydrate($this->productListing->serialize());
        $this->assertEquals($rehydrated, $this->productListing);
    }
}
