<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService;

use LizardsAndPumpkins\Context\Locale\Locale;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Price\Price;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices
 * @uses   \LizardsAndPumpkins\Import\Price\Price
 */
class EnrichProductJsonWithPricesTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $stubContext;

    /**
     * @var EnrichProductJsonWithPrices
     */
    private $enrichProductJsonWithPrices;

    /**
     * @param mixed $attributeCode
     * @param mixed $expectedValue
     * @param string[] $attributeData
     */
    private function assertProductJsonDataHas($attributeCode, $expectedValue, array $attributeData): void
    {
        $this->assertArrayHasKey($attributeCode, $attributeData);
        $this->assertSame($expectedValue, $attributeData[$attributeCode]);
    }

    private function getPriceAsFractionUnits(string $amount) : int
    {
        return Price::fromDecimalValue($amount)->getAmount();
    }

    final protected function setUp(): void
    {
        $this->stubContext = $this->createMock(Context::class);
        $this->stubContext->method('getValue')->willReturnMap([[Locale::CONTEXT_CODE, 'de_DE']]);

        $this->enrichProductJsonWithPrices = new EnrichProductJsonWithPrices();
    }

    public function testItEnrichesProductDataWithPriceAndSpecialPriceInformation(): void
    {
        $productData = [];
        $price = $this->getPriceAsFractionUnits('19.99');
        $specialPrice = $this->getPriceAsFractionUnits('17.99');

        $result = $this->enrichProductJsonWithPrices->addPricesToProductData(
            $this->stubContext,
            $productData,
            $price,
            $specialPrice
        );

        $this->assertProductJsonDataHas('price', '19,99 €', $result['attributes']);
        $this->assertProductJsonDataHas('raw_price', 1999, $result['attributes']);
        $this->assertProductJsonDataHas('special_price', '17,99 €', $result['attributes']);
        $this->assertProductJsonDataHas('raw_special_price', 1799, $result['attributes']);
        $this->assertProductJsonDataHas('price_currency', 'EUR', $result['attributes']);
        $this->assertProductJsonDataHas('price_faction_digits', 2, $result['attributes']);
        $this->assertProductJsonDataHas('price_base_unit', 100, $result['attributes']);
    }

    public function testItDoesNotAddSpecialPriceDataIfTheSpecialPriceIsNull(): void
    {
        $productData = [];
        $price = '1999';
        $specialPrice = null;

        $result = $this->enrichProductJsonWithPrices->addPricesToProductData(
            $this->stubContext,
            $productData,
            $price,
            $specialPrice
        );
        
        $this->assertArrayNotHasKey('special_price', $result['attributes']);
        $this->assertArrayNotHasKey('raw_special_price', $result['attributes']);
    }

    public function testItAddsCurrencyInformationToTheProductAttributes(): void
    {
        $productData = [];
        $price = '1999';
        $specialPrice = '1799';

        $result = $this->enrichProductJsonWithPrices->addPricesToProductData(
            $this->stubContext,
            $productData,
            $price,
            $specialPrice
        );

        $this->assertProductJsonDataHas('price_currency', 'EUR', $result['attributes']);
        $this->assertProductJsonDataHas('price_faction_digits', 2, $result['attributes']);
        $this->assertProductJsonDataHas('price_base_unit', 100, $result['attributes']);
    }
}
