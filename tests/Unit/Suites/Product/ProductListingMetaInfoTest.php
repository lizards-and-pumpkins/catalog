<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\UrlKey;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingMetaInfo
 */
class ProductListingMetaInfoTest extends \PHPUnit_Framework_TestCase
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
     * @var ProductListingMetaInfo
     */
    private $productListingMetaInfo;

    protected function setUp()
    {
        $this->stubUrlKey = $this->getMock(UrlKey::class, [], [], '', false);
        $this->stubCriteria = $this->getMock(SearchCriteria::class);
        $this->productListingMetaInfo = new ProductListingMetaInfo(
            $this->stubUrlKey,
            $this->dummyContextData,
            $this->stubCriteria
        );
    }

    public function testProductListingUrlKeyIsReturned()
    {
        $result = $this->productListingMetaInfo->getUrlKey();
        $this->assertSame($this->stubUrlKey, $result);
    }

    public function testProductListingContextDataIsReturned()
    {
        $result = $this->productListingMetaInfo->getContextData();
        $this->assertSame($this->dummyContextData, $result);
    }

    public function testProductListingCriteriaAreReturned()
    {
        $result = $this->productListingMetaInfo->getCriteria();
        $this->assertSame($this->stubCriteria, $result);
    }
}
