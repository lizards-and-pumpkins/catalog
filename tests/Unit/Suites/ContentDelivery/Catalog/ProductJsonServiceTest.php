<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\SnippetTransformation\Exception\NoValidLocaleInContextException;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\SnippetKeyGenerator;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductJsonService
 */
class ProductJsonServiceTest extends \PHPUnit_Framework_TestCase
{
    private $dummyProductData = ['attributes' => []];

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductJsonSnippetKeyGenerator;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubPriceSnippetKeyGenerator;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSpecialPriceSnippetKeyGenerator;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var ProductJsonService
     */
    private $productJsonService;

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
        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->stubProductJsonSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubPriceSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubSpecialPriceSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubContext = $this->getMock(Context::class);

        $this->productJsonService = new ProductJsonService(
            $this->mockDataPoolReader,
            $this->stubProductJsonSnippetKeyGenerator,
            $this->stubPriceSnippetKeyGenerator,
            $this->stubSpecialPriceSnippetKeyGenerator,
            $this->stubContext
        );
    }

    public function testExceptionIsThrownIfContextDoesNotHaveLocaleData()
    {
        $jsonSnippetKey = 'dummy_json_snippet';
        $priceSnippetKey = 'dummy_price_snippet_key';
        $specialPriceSnippetKey = 'dummy_special_price_snippet_key';

        $this->stubProductJsonSnippetKeyGenerator->method('getKeyForContext')->willReturn($jsonSnippetKey);
        $this->stubPriceSnippetKeyGenerator->method('getKeyForContext')->willReturn($priceSnippetKey);
        $this->stubSpecialPriceSnippetKeyGenerator->method('getKeyForContext')->willReturn($specialPriceSnippetKey);

        $this->mockDataPoolReader->method('getSnippets')->willReturn([
            $jsonSnippetKey => json_encode($this->dummyProductData),
            $priceSnippetKey => '9999',
            $specialPriceSnippetKey => '8999',
        ]);

        $this->setExpectedException(NoValidLocaleInContextException::class);

        $productId = $this->getMock(ProductId::class, [], [], '', false);
        $this->productJsonService->get($productId);
    }

    public function testItDelegatesToTheDataPoolReaderToFetchTheProductData()
    {
        $jsonSnippetKey = 'dummy_json_snippet';
        $priceSnippetKey = 'dummy_price_snippet_key';
        $specialPriceSnippetKey = 'dummy_special_price_snippet_key';

        $this->stubContext->method('getValue')->willReturnMap([[ContextLocale::CODE, 'de_DE']]);

        $this->stubProductJsonSnippetKeyGenerator->method('getKeyForContext')->willReturn($jsonSnippetKey);
        $this->stubPriceSnippetKeyGenerator->method('getKeyForContext')->willReturn($priceSnippetKey);
        $this->stubSpecialPriceSnippetKeyGenerator->method('getKeyForContext')->willReturn($specialPriceSnippetKey);
        
        $this->mockDataPoolReader->expects($this->once())
            ->method('getSnippets')->with([$jsonSnippetKey, $priceSnippetKey, $specialPriceSnippetKey])
            ->willReturn([
                $jsonSnippetKey => json_encode($this->dummyProductData),
                $priceSnippetKey => '9999',
                $specialPriceSnippetKey => '8999',
            ]);
        
        $productId = $this->getMock(ProductId::class, [], [], '', false);
        
        $this->productJsonService->get($productId);
    }

    public function testItReturnsTheProductDataIncludingTheProductPrice()
    {
        $jsonSnippetKey = 'dummy_json_snippet';
        $priceSnippetKey = 'dummy_price_snippet_key';
        $specialPriceSnippetKey = 'dummy_special_price_snippet_key';

        $this->stubContext->method('getValue')->willReturnMap([[ContextLocale::CODE, 'de_DE']]);

        $this->stubProductJsonSnippetKeyGenerator->method('getKeyForContext')->willReturn($jsonSnippetKey);
        $this->stubPriceSnippetKeyGenerator->method('getKeyForContext')->willReturn($priceSnippetKey);
        $this->stubSpecialPriceSnippetKeyGenerator->method('getKeyForContext')->willReturn($specialPriceSnippetKey);
        
        $this->mockDataPoolReader
            ->method('getSnippets')
            ->willReturn([
                $jsonSnippetKey => json_encode($this->dummyProductData),
                $priceSnippetKey => '9999',
                $specialPriceSnippetKey => '8999',
            ]);

        $productId = $this->getMock(ProductId::class, [], [], '', false);
        
        $result = $this->productJsonService->get($productId);
        
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('attributes', $result[0]);
        
        $this->assertProductJsonDataHas('price', '99,99 €', $result[0]['attributes']);
        $this->assertProductJsonDataHas('raw_price', '9999', $result[0]['attributes']);
        $this->assertProductJsonDataHas('special_price', '89,99 €', $result[0]['attributes']);
        $this->assertProductJsonDataHas('raw_special_price', '8999', $result[0]['attributes']);
    }

    public function testItAllowsPassingTheSnippetsFromTheOutside()
    {
        $productData = [];
        $price = '1999';
        $specialPrice = '1799';
        $this->stubContext->method('getValue')->willReturnMap([[ContextLocale::CODE, 'de_DE']]);
        
        $result = $this->productJsonService->addGivenPricesToProductData($productData, $price, $specialPrice, 'EUR');
        
        $this->assertProductJsonDataHas('price', '19,99 €', $result['attributes']);
        $this->assertProductJsonDataHas('raw_price', '1999', $result['attributes']);
        $this->assertProductJsonDataHas('special_price', '17,99 €', $result['attributes']);
        $this->assertProductJsonDataHas('raw_special_price', '1799', $result['attributes']);
        $this->assertProductJsonDataHas('price_currency', 'EUR', $result['attributes']);
        $this->assertProductJsonDataHas('price_faction_digits', 2, $result['attributes']);
        $this->assertProductJsonDataHas('price_base_unit', 100, $result['attributes']);
    }

    public function testItAddsCurrencyInformationToTheProductAttributes()
    {
        $productData = [];
        $price = '1999';
        $specialPrice = '1799';
        $this->stubContext->method('getValue')->willReturnMap([[ContextLocale::CODE, 'de_DE']]);

        $result = $this->productJsonService->addGivenPricesToProductData($productData, $price, $specialPrice, 'EUR');

        $this->assertProductJsonDataHas('price_currency', 'EUR', $result['attributes']);
        $this->assertProductJsonDataHas('price_faction_digits', 2, $result['attributes']);
        $this->assertProductJsonDataHas('price_base_unit', 100, $result['attributes']);
    }
}
