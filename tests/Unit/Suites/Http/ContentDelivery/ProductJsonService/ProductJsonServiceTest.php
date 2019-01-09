<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\Locale\Locale;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\Exception\SnippetNotFoundException;
use LizardsAndPumpkins\Import\Product\ProductId;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService
 */
class ProductJsonServiceTest extends TestCase
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
     * @var EnrichProductJsonWithPrices|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubEnrichProductJsonWithPrices;

    /**
     * @var ProductJsonService
     */
    private $productJsonService;

    protected function setUp()
    {
        $this->mockDataPoolReader = $this->createMock(DataPoolReader::class);
        $this->stubProductJsonSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->stubPriceSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->stubSpecialPriceSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->stubEnrichProductJsonWithPrices = $this->createMock(EnrichProductJsonWithPrices::class);

        $this->productJsonService = new ProductJsonService(
            $this->mockDataPoolReader,
            $this->stubProductJsonSnippetKeyGenerator,
            $this->stubPriceSnippetKeyGenerator,
            $this->stubSpecialPriceSnippetKeyGenerator,
            $this->stubEnrichProductJsonWithPrices
        );
    }

    public function testItDelegatesToTheDataPoolReaderToFetchTheProductData()
    {
        $jsonSnippetKey = 'dummy_json_snippet';
        $priceSnippetKey = 'dummy_price_snippet_key';
        $specialPriceSnippetKey = 'dummy_special_price_snippet_key';

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        $stubContext->method('getValue')->willReturnMap([[Locale::CONTEXT_CODE, 'de_DE']]);

        $this->stubProductJsonSnippetKeyGenerator->method('getKeyForContext')->willReturn($jsonSnippetKey);
        $this->stubPriceSnippetKeyGenerator->method('getKeyForContext')->willReturn($priceSnippetKey);
        $this->stubSpecialPriceSnippetKeyGenerator->method('getKeyForContext')->willReturn($specialPriceSnippetKey);

        $this->mockDataPoolReader->expects($this->once())
            ->method('getSnippets')->with([$jsonSnippetKey, $priceSnippetKey, $specialPriceSnippetKey])
            ->willReturn([
                $jsonSnippetKey => json_encode($this->dummyProductData),
                $priceSnippetKey => '1199',
                $specialPriceSnippetKey => '999',
            ]);

        $stubProductId = $this->createMock(ProductId::class);

        $this->productJsonService->get($stubContext, '', $stubProductId);
    }

    public function testItReturnsTheEnrichedProductData()
    {
        $jsonSnippetKey = 'dummy_json_snippet';
        $priceSnippetKey = 'dummy_price_snippet_key';
        $specialPriceSnippetKey = 'dummy_special_price_snippet_key';

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        $stubContext->method('getValue')->willReturnMap([[Locale::CONTEXT_CODE, 'de_DE']]);

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

        $expected = ['dummy enriched data'];
        $this->stubEnrichProductJsonWithPrices->method('addPricesToProductData')
            ->with($stubContext, $this->dummyProductData, '9999', '8999')
            ->willReturn($expected);

        $stubProductId = $this->createMock(ProductId::class);

        $result = $this->productJsonService->get($stubContext, '', $stubProductId);

        $this->assertContains($expected, $result);
    }

    public function testItThrowsAnExceptionIfKeyValueDoesNotContainSnippet()
    {
        $jsonSnippet = null;
        $priceSnippet = '99';
        $specialPriceSnippet = '89';

        $jsonSnippetKey = 'dummy_json_snippet';
        $priceSnippetKey = 'dummy_price_snippet_key';
        $specialPriceSnippetKey = 'dummy_special_price_snippet_key';

        $this->expectException(SnippetNotFoundException::class);
        $this->expectExceptionMessage(sprintf('Snippet with key %s not found.', $jsonSnippetKey));

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        $stubContext->method('getValue')->willReturnMap([[Locale::CONTEXT_CODE, 'de_DE']]);

        $this->stubProductJsonSnippetKeyGenerator->method('getKeyForContext')->willReturn($jsonSnippetKey);
        $this->stubPriceSnippetKeyGenerator->method('getKeyForContext')->willReturn($priceSnippetKey);
        $this->stubSpecialPriceSnippetKeyGenerator->method('getKeyForContext')->willReturn($specialPriceSnippetKey);

        $this->mockDataPoolReader
            ->method('getSnippets')
            ->willReturn([
                $jsonSnippetKey => $jsonSnippet,
                $priceSnippetKey => $priceSnippet,
                $specialPriceSnippetKey => $specialPriceSnippet,
            ]);

        $stubProductId = $this->createMock(ProductId::class);

        $this->productJsonService->get($stubContext, '', $stubProductId);
    }

    public function testPassSnippetNameToKeyGenerator()
    {
        $jsonSnippetKey = 'dummy_json_snippet';
        $priceSnippetKey = 'dummy_price_snippet_key';
        $specialPriceSnippetKey = 'dummy_special_price_snippet_key';

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        $stubContext->method('getValue')->willReturnMap([[Locale::CONTEXT_CODE, 'de_DE']]);

        $this->stubProductJsonSnippetKeyGenerator->method('getKeyForContext')
            ->with($this->anything(),$this->arrayHasKey(ProductJsonService::SNIPPET_NAME))
            ->willReturn($jsonSnippetKey);
        $this->stubPriceSnippetKeyGenerator->method('getKeyForContext')->willReturn($priceSnippetKey);
        $this->stubSpecialPriceSnippetKeyGenerator->method('getKeyForContext')->willReturn($specialPriceSnippetKey);

        $this->mockDataPoolReader->expects($this->once())
            ->method('getSnippets')->with([$jsonSnippetKey, $priceSnippetKey, $specialPriceSnippetKey])
            ->willReturn([
                $jsonSnippetKey => json_encode($this->dummyProductData),
                $priceSnippetKey => '1199',
                $specialPriceSnippetKey => '999',
            ]);

        $stubProductId = $this->createMock(ProductId::class);

        $this->productJsonService->get($stubContext, '', $stubProductId);
    }
}
