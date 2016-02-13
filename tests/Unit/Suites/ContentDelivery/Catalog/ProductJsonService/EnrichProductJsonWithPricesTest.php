<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductJsonService;

use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\Exception\NoValidLocaleInContextException;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductJsonService\EnrichProductJsonWithPrices
 */
class EnrichProductJsonWithPricesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var EnrichProductJsonWithPrices
     */
    private $enrichProductJsonWithPrices;

    /**
     * @param string $attributeCode
     * @param string $expectedValue
     * @param string[] $attributeData
     */
    private function assertProductJsonDataHas($attributeCode, $expectedValue, array $attributeData)
    {
        $this->assertArrayHasKey($attributeCode, $attributeData);
        $this->assertSame($expectedValue, $attributeData[$attributeCode]);
    }

    protected function setUp()
    {
        $this->stubContext = $this->getMock(Context::class);
        $this->enrichProductJsonWithPrices = new EnrichProductJsonWithPrices($this->stubContext);
    }

    public function testExceptionIsThrownIfContextDoesNotHaveLocaleData()
    {
        $this->setExpectedException(NoValidLocaleInContextException::class, 'No locale found in context');
        
        $this->stubContext->method('getValue')->willReturn(null);

        $productData = [];
        $price = 10;
        $specialPrice = 9;
        $this->enrichProductJsonWithPrices->addPricesToProductData($productData, $price, $specialPrice);
    }

    public function testItEnrichesProductDataWithPriceAndSpecialPriceInformation()
    {
        $productData = [];
        $price = 19990000;
        $specialPrice = 17990000;
        
        $this->stubContext->method('getValue')->willReturnMap([[ContextLocale::CODE, 'de_DE']]);

        $result = $this->enrichProductJsonWithPrices->addPricesToProductData($productData, $price, $specialPrice);

        $this->assertProductJsonDataHas('price', '19,99 €', $result['attributes']);
        $this->assertProductJsonDataHas('raw_price', 1999, $result['attributes']);
        $this->assertProductJsonDataHas('special_price', '17,99 €', $result['attributes']);
        $this->assertProductJsonDataHas('raw_special_price', 1799, $result['attributes']);
        $this->assertProductJsonDataHas('price_currency', 'EUR', $result['attributes']);
        $this->assertProductJsonDataHas('price_faction_digits', 2, $result['attributes']);
        $this->assertProductJsonDataHas('price_base_unit', 100, $result['attributes']);
    }

    public function testItDoesNotAddSpecialPriceDataIfTheSpecialPriceIsNull()
    {
        $productData = [];
        $price = '1999';
        $specialPrice = null;
        
        $this->stubContext->method('getValue')->willReturnMap([[ContextLocale::CODE, 'de_DE']]);

        $result = $this->enrichProductJsonWithPrices->addPricesToProductData($productData, $price, $specialPrice);
        
        $this->assertArrayNotHasKey('special_price', $result['attributes']);
        $this->assertArrayNotHasKey('raw_special_price', $result['attributes']);
    }

    public function testItAddsCurrencyInformationToTheProductAttributes()
    {
        $productData = [];
        $price = '1999';
        $specialPrice = '1799';
        $this->stubContext->method('getValue')->willReturnMap([[ContextLocale::CODE, 'de_DE']]);

        $result = $this->enrichProductJsonWithPrices->addPricesToProductData($productData, $price, $specialPrice);

        $this->assertProductJsonDataHas('price_currency', 'EUR', $result['attributes']);
        $this->assertProductJsonDataHas('price_faction_digits', 2, $result['attributes']);
        $this->assertProductJsonDataHas('price_base_unit', 100, $result['attributes']);
    }
}
