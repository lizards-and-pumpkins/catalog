<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\UrlKey;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListing
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
     * @var ProductListing
     */
    private $productListing;

    protected function setUp()
    {
        $this->stubUrlKey = $this->getMock(UrlKey::class, [], [], '', false);
        $this->stubCriteria = $this->getMock(SearchCriteria::class);
        $this->productListing = new ProductListing(
            $this->stubUrlKey,
            $this->dummyContextData,
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
}
