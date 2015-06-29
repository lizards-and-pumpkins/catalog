<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductListingSource
 */
class ProductListingSourceTest extends \PHPUnit_Framework_TestCase
{
    public function testProductListingUrlKeyIsReturned()
    {
        $stubUrlKey = 'foo';
        $stubContextData = [];
        $stubCriteria = [];

        $productListingSource = new ProductListingSource($stubUrlKey, $stubContextData, $stubCriteria);
        $result = $productListingSource->getUrlKey();

        $this->assertSame($stubUrlKey, $result);
    }

    public function testProductListingContextDataIsReturned()
    {
        $stubUrlKey = '';
        $stubContextData = ['foo' => 'bar', 'baz' => 'qux'];
        $stubCriteria = [];

        $productListingSource = new ProductListingSource($stubUrlKey, $stubContextData, $stubCriteria);
        $result = $productListingSource->getContextData();

        $this->assertSame($stubContextData, $result);
    }

    public function testProductListingCriteriaAreReturned()
    {
        $stubUrlKey = '';
        $stubContextData = [];
        $stubCriteria = ['foo' => 'bar', 'baz' => 'qux'];

        $productListingSource = new ProductListingSource($stubUrlKey, $stubContextData, $stubCriteria);
        $result = $productListingSource->getCriteria();

        $this->assertSame($stubCriteria, $result);
    }
}
