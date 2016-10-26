<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation;

use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\ProductJsonSnippetTransformation
 */
class ProductJsonSnippetTransformationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductJsonSnippetTransformation
     */
    private $transformation;

    /**
     * @var EnrichProductJsonWithPrices|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockEnrichesProductJsonWithPrices;

    /**
     * @var PageSnippets|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubPageSnippets;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @param mixed $expected
     * @param mixed $input
     */
    private function assertTransformation($expected, $input)
    {
        $callable = $this->transformation;
        $result = call_user_func($callable, $input, $this->stubContext, $this->stubPageSnippets);
        $this->assertSame($expected, $result);
    }

    protected function setUp()
    {
        $class = EnrichProductJsonWithPrices::class;
        $this->mockEnrichesProductJsonWithPrices = $this->createMock($class);
        $this->transformation = new ProductJsonSnippetTransformation($this->mockEnrichesProductJsonWithPrices);

        $this->stubPageSnippets = $this->createMock(PageSnippets::class);
        $this->stubContext = $this->createMock(Context::class);
    }
    
    public function testItIsASnippetTransformation()
    {
        $this->assertInstanceOf(SnippetTransformation::class, $this->transformation);
    }

    public function testItUsesDelegateClassToEnrichProductJsonWithPrices()
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

    public function testItPassesNullIfNoSpecialPriceSnippetIsPresent()
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
            ->method('addPricesToProductData')->with([], '999', null)
            ->willReturn($enrichedProductData);

        $this->assertTransformation(json_encode($enrichedProductData), $inputJson);
    }
}
