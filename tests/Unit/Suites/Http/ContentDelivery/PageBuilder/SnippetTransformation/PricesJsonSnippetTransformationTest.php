<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation;

use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Context\Context;

/**
 * @covers \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\PricesJsonSnippetTransformation
 */
class PricesJsonSnippetTransformationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PricesJsonSnippetTransformation
     */
    private $pricesJsonSnippetTransformation;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var SnippetTransformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubPriceSnippetTransformation;

    /**
     * @var PageSnippets|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubPageSnippets;

    /**
     * @param mixed $expected
     * @param mixed $input
     */
    private function assertTransformation($expected, $input)
    {
        $callable = $this->pricesJsonSnippetTransformation;
        $result = call_user_func($callable, $input, $this->stubContext, $this->stubPageSnippets);
        $this->assertSame($expected, $result);
    }

    protected function setUp()
    {
        $this->stubPageSnippets = $this->createMock(PageSnippets::class);
        $this->stubContext = $this->createMock(Context::class);
        $this->stubPriceSnippetTransformation = $this->createMock(SnippetTransformation::class);
        $this->pricesJsonSnippetTransformation = new PricesJsonSnippetTransformation(
            $this->stubPriceSnippetTransformation
        );
    }

    public function testItIsASnippetTransformation()
    {
        $this->assertInstanceOf(SnippetTransformation::class, $this->pricesJsonSnippetTransformation);
    }

    public function testItReturnsAnEmptyStringIfInputIsNotString()
    {
        $this->assertTransformation('', 123);
    }

    public function testItReturnsAnEmptyStringIfInputIsNotValidJsonArray()
    {
        $this->assertTransformation('', '"a json string"');
    }

    public function testItReturnsAnEmptyJsonArrayIfTheInputJsonArrayIsEmpty()
    {
        $this->assertTransformation('[]', '[]');
    }

    public function testItDelegatesToThePriceSnippetTransformationForEachArrayElement()
    {
        $transformedPrice = '9000 EUR';
        $this->stubPriceSnippetTransformation->method('__invoke')->willReturn($transformedPrice);
        $input = json_encode([['123'], ['456', '789']]);
        $expected = json_encode([[$transformedPrice], [$transformedPrice, $transformedPrice]]);
        $this->assertTransformation($expected, $input);
    }
}
