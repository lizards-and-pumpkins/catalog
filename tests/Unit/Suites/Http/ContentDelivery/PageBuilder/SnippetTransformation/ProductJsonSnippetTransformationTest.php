<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation;

use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\ProductJsonSnippetTransformation
 */
class ProductJsonSnippetTransformationTest extends TestCase
{
    /**
     * @var ProductJsonSnippetTransformation
     */
    private $transformation;

    /**
     * @var EnrichProductJsonWithPrices|MockObject
     */
    private $mockEnrichesProductJsonWithPrices;

    /**
     * @var PageSnippets|MockObject
     */
    private $stubPageSnippets;

    /**
     * @var Context|MockObject
     */
    private $stubContext;

    /**
     * @param mixed $expected
     * @param mixed $input
     */
    private function assertTransformation($expected, $input): void
    {
        $callable = $this->transformation;
        $result = call_user_func($callable, $input, $this->stubContext, $this->stubPageSnippets);
        $this->assertSame($expected, $result);
    }

    final protected function setUp(): void
    {
        $class = EnrichProductJsonWithPrices::class;
        $this->mockEnrichesProductJsonWithPrices = $this->createMock($class);
        $this->transformation = new ProductJsonSnippetTransformation($this->mockEnrichesProductJsonWithPrices);

        $this->stubPageSnippets = $this->createMock(PageSnippets::class);
        $this->stubContext = $this->createMock(Context::class);
    }
    
    public function testItIsASnippetTransformation(): void
    {
        $this->assertInstanceOf(SnippetTransformation::class, $this->transformation);
    }

    public function testItUsesDelegateClassToEnrichProductJsonWithPrices(): void
    {
        $inputJson = json_encode([]);
        $enrichedProductData = ['dummy enriched product data'];
        
        $this->stubPageSnippets->method('hasSnippetCode')->willReturnMap([
            [PriceSnippetRenderer::SPECIAL_PRICE, true],
        ]);
        $this->stubPageSnippets->method('getSnippetByCode')->willReturnMap([
            [PriceSnippetRenderer::PRICE, '999'],
            [PriceSnippetRenderer::SPECIAL_PRICE, '799'],
        ]);
        
        $this->mockEnrichesProductJsonWithPrices->expects($this->once())
            ->method('addPricesToProductData')
            ->willReturn($enrichedProductData);
        
        $this->assertTransformation(json_encode($enrichedProductData), $inputJson);
    }

    public function testItPassesNullIfNoSpecialPriceSnippetIsPresent(): void
    {
        $inputJson = json_encode([]);
        $enrichedProductData = ['dummy enriched product data'];

        $this->stubPageSnippets->method('hasSnippetCode')->willReturnMap([
            [PriceSnippetRenderer::SPECIAL_PRICE, false],
        ]);
        $this->stubPageSnippets->method('getSnippetByCode')->willReturnMap([
            [PriceSnippetRenderer::PRICE, '999'],
        ]);

        $this->mockEnrichesProductJsonWithPrices->expects($this->once())
            ->method('addPricesToProductData')->with($this->stubContext, [], '999', null)
            ->willReturn($enrichedProductData);

        $this->assertTransformation(json_encode($enrichedProductData), $inputJson);
    }
}
