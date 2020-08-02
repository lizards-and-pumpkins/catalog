<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation;

use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Context\Context;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\PricesJsonSnippetTransformation
 */
class PricesJsonSnippetTransformationTest extends TestCase
{
    /**
     * @var PricesJsonSnippetTransformation
     */
    private $pricesJsonSnippetTransformation;

    /**
     * @var Context|MockObject
     */
    private $stubContext;

    /**
     * @var SnippetTransformation|MockObject
     */
    private $stubPriceSnippetTransformation;

    /**
     * @var PageSnippets|MockObject
     */
    private $stubPageSnippets;

    /**
     * @param mixed $expected
     * @param mixed $input
     */
    private function assertTransformation($expected, $input): void
    {
        $callable = $this->pricesJsonSnippetTransformation;
        $result = call_user_func($callable, $input, $this->stubContext, $this->stubPageSnippets);
        $this->assertSame($expected, $result);
    }

    final protected function setUp(): void
    {
        $this->stubPageSnippets = $this->createMock(PageSnippets::class);
        $this->stubContext = $this->createMock(Context::class);
        $this->stubPriceSnippetTransformation = $this->createMock(SnippetTransformation::class);
        $this->pricesJsonSnippetTransformation = new PricesJsonSnippetTransformation(
            $this->stubPriceSnippetTransformation
        );
    }

    public function testItIsASnippetTransformation(): void
    {
        $this->assertInstanceOf(SnippetTransformation::class, $this->pricesJsonSnippetTransformation);
    }

    public function testItReturnsAnEmptyStringIfInputIsNotString(): void
    {
        $this->assertTransformation('', 123);
    }

    public function testItReturnsAnEmptyStringIfInputIsNotValidJsonArray(): void
    {
        $this->assertTransformation('', '"a json string"');
    }

    public function testItReturnsAnEmptyJsonArrayIfTheInputJsonArrayIsEmpty(): void
    {
        $this->assertTransformation('[]', '[]');
    }

    public function testItDelegatesToThePriceSnippetTransformationForEachArrayElement(): void
    {
        $transformedPrice = '9000 EUR';
        $this->stubPriceSnippetTransformation->method('__invoke')->willReturn($transformedPrice);
        $input = json_encode([['123'], ['456', '789']]);
        $expected = json_encode([[$transformedPrice], [$transformedPrice, $transformedPrice]]);
        $this->assertTransformation($expected, $input);
    }
}
