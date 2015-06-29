<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \Brera\Product\ProductListingSource
 */
class ProductListingSourceTest extends \PHPUnit_Framework_TestCase
{
    public function testProductListingUrlKeyIsReturned()
    {
        $stubUrlKey = 'foo';
        $stubContextData = [];
        $stubCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);

        $productListingSource = new ProductListingSource($stubUrlKey, $stubContextData, $stubCriteria);
        $result = $productListingSource->getUrlKey();

        $this->assertSame($stubUrlKey, $result);
    }

    public function testProductListingContextDataIsReturned()
    {
        $stubUrlKey = '';
        $stubContextData = ['foo' => 'bar', 'baz' => 'qux'];
        $stubCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);

        $productListingSource = new ProductListingSource($stubUrlKey, $stubContextData, $stubCriteria);
        $result = $productListingSource->getContextData();

        $this->assertSame($stubContextData, $result);
    }

    public function testProductListingCriteriaAreReturned()
    {
        $stubUrlKey = '';
        $stubContextData = [];
        $stubCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);

        $productListingSource = new ProductListingSource($stubUrlKey, $stubContextData, $stubCriteria);
        $result = $productListingSource->getCriteria();

        $this->assertSame($stubCriteria, $result);
    }
}
