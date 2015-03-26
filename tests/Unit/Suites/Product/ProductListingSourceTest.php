<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductListingSource
 */
class ProductListingSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnProductListingUrlKey()
    {
        $stubUrlKey = 'foo';
        $stubContextData = [];
        $stubCriteria = [];

        $productListingSource = new ProductListingSource($stubUrlKey, $stubContextData, $stubCriteria);
        $result = $productListingSource->getUrlKey();

        $this->assertSame($stubUrlKey, $result);
    }
}
