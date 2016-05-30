<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKey;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListing
 */
class ProductListingTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->stubUrlKey = $this->getMock(UrlKey::class, [], [], '', false);
        $this->stubCriteria = $this->getMock(SearchCriteria::class);
        $this->stubProductListingAttributeList = $this->getMock(ProductListingAttributeList::class, [], [], '', false);

        $this->productListing = new ProductListing(
            $this->stubUrlKey,
            $this->dummyContextData,
            $this->stubProductListingAttributeList,
            $this->stubCriteria
        );
    }

    public function testProductListingUrlKeyIsReturned()
    {
        $result = $this->productListing->getUrlKey();
        $this->assertSame($this->stubUrlKey, $result);
    }

    public function testProductListingContextDataIsReturned()
    {
        $result = $this->productListing->getContextData();
        $this->assertSame($this->dummyContextData, $result);
    }

    public function testProductListingIsReturned()
    {
        $result = $this->productListing->getCriteria();
        $this->assertSame($this->stubCriteria, $result);
    }

    public function testCheckingForProductListingAttributeExistenceIsDelegatedToProductAttributeList()
    {
        $this->stubProductListingAttributeList->method('hasAttribute')->willReturn(false);
        $this->assertFalse($this->productListing->hasAttribute('foo'));
    }

    public function testProductListingAttributeValueIsReturned()
    {
        $attributeCode = 'foo';
        $attributeValue = 'bar';

        $this->stubProductListingAttributeList->method('hasAttribute')->willReturn(true);
        $this->stubProductListingAttributeList->method('getAttributeValueByCode')->willReturn($attributeValue);

        $this->assertSame($attributeValue, $this->productListing->getAttributeValueByCode($attributeCode));
    }

    public function testCanBeSerializedAndRehydrated()
    {
        $rehydrated = ProductListing::rehydrate($this->productListing->serialize());
        $this->assertEquals($rehydrated, $this->productListing);
    }
}
