<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation;

use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\SnippetCode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\ProductJsonSnippetTransformation
 * @uses   \LizardsAndPumpkins\Import\SnippetCode
 */
class ProductJsonSnippetTransformationTest extends TestCase
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
        
        $this->stubPageSnippets->method('hasSnippetCode')->willReturn(true);
        $this->stubPageSnippets->method('getSnippetByCode')->willReturnCallback(function (SnippetCode $snippetCode) {
            if (PriceSnippetRenderer::PRICE === (string) $snippetCode) {
                return '999';
            }

            if (PriceSnippetRenderer::SPECIAL_PRICE === (string) $snippetCode) {
                return '799';
            }

            throw new \LogicException(sprintf('Test is not expecting snippet code "%s".', $snippetCode));
        });

        $this->mockEnrichesProductJsonWithPrices->expects($this->once())
            ->method('addPricesToProductData')
            ->willReturn($enrichedProductData);
        
        $this->assertTransformation(json_encode($enrichedProductData), $inputJson);
    }

    public function testItPassesNullIfNoSpecialPriceSnippetIsPresent()
    {
        $inputJson = json_encode([]);
        $enrichedProductData = ['dummy enriched product data'];

        $this->stubPageSnippets->method('hasSnippetCode')->willReturn(false);
        $this->stubPageSnippets->method('getSnippetByCode')->willReturnCallback(function (SnippetCode $snippetCode) {
            if (PriceSnippetRenderer::PRICE === (string) $snippetCode) {
                return '999';
            }

            throw new \LogicException(sprintf('Test is not expecting snippet code "%s".', $snippetCode));
        });

        $this->mockEnrichesProductJsonWithPrices->expects($this->once())
            ->method('addPricesToProductData')->with($this->stubContext, [], '999', null)
            ->willReturn($enrichedProductData);

        $this->assertTransformation(json_encode($enrichedProductData), $inputJson);
    }
}
