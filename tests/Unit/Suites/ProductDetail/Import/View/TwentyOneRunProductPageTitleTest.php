<?php

namespace LizardsAndPumpkins\ProductDetail\Import\View;

use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\ProductDetail\Import\View\TwentyOneRunProductPageTitle;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\Import\View\TwentyOneRunProductPageTitle
 */
class TwentyOneRunProductPageTitleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductView|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductView;

    /**
     * @var TwentyOneRunProductPageTitle
     */
    private $productPageTitle;

    protected function setUp()
    {
        $this->stubProductView = $this->getMock(ProductView::class);
        $this->productPageTitle = new TwentyOneRunProductPageTitle();
    }

    /**
     * @dataProvider requiredAttributeCodeProvider
     * @param string $requiredAttributeCode
     */
    public function testProductTitleContainsRequiredProductAttributes($requiredAttributeCode)
    {
        $testAttributeValue = 'foo';

        $this->stubProductView->method('getFirstValueOfAttribute')->willReturnCallback(
            function ($attributeCode) use ($requiredAttributeCode, $testAttributeValue) {
                if ($attributeCode === $requiredAttributeCode) {
                    return $testAttributeValue;
                }
                return '';
            }
        );

        $this->assertContains($testAttributeValue, $this->productPageTitle->forProductView($this->stubProductView));
    }

    /**
     * @return array[]
     */
    public function requiredAttributeCodeProvider()
    {
        return [
            ['name'],
            ['product_group'],
            ['brand'],
            ['style'],
        ];
    }

    public function testProductTitleContainsProductTitleSuffix()
    {
        $result = $this->productPageTitle->forProductView($this->stubProductView);
        $this->assertContains(TwentyOneRunProductPageTitle::PRODUCT_TITLE_SUFFIX, $result);
    }

    public function testProductMetaTitleIsNotExceedingDefinedLimit()
    {
        $maxTitleLength = TwentyOneRunProductPageTitle::MAX_PRODUCT_TITLE_LENGTH;
        $attributeLength = ($maxTitleLength - strlen(TwentyOneRunProductPageTitle::PRODUCT_TITLE_SUFFIX)) / 2 - 1;

        $this->stubProductView->method('getFirstValueOfAttribute')->willReturn(str_repeat('-', $attributeLength));

        $result = $this->productPageTitle->forProductView($this->stubProductView);

        $this->assertLessThanOrEqual($maxTitleLength, strlen($result));
    }

}
