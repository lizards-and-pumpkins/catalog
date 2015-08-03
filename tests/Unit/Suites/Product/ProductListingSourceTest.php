<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\ProjectionSourceData;
use Brera\UrlKey;

/**
 * @covers \Brera\Product\ProductListingSource
 */
class ProductListingSourceTest extends \PHPUnit_Framework_TestCase
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
     * @var ProductListingSource
     */
    private $productListingSource;

    protected function setUp()
    {
        $this->stubUrlKey = $this->getMock(UrlKey::class, [], [], '', false);
        $this->stubCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);
        $this->productListingSource = new ProductListingSource(
            $this->stubUrlKey,
            $this->dummyContextData,
            $this->stubCriteria
        );
    }

    public function testProjectionSourceDataInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ProjectionSourceData::class, $this->productListingSource);
    }

    public function testProductListingUrlKeyIsReturned()
    {
        $result = $this->productListingSource->getUrlKey();
        $this->assertSame($this->stubUrlKey, $result);
    }

    public function testProductListingContextDataIsReturned()
    {
        $result = $this->productListingSource->getContextData();
        $this->assertSame($this->dummyContextData, $result);
    }

    public function testProductListingCriteriaAreReturned()
    {
        $result = $this->productListingSource->getCriteria();
        $this->assertSame($this->stubCriteria, $result);
    }
}
