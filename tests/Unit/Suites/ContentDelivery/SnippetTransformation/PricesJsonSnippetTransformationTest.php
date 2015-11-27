<?php

namespace LizardsAndPumpkins\ContentDelivery\SnippetTransformation;

use LizardsAndPumpkins\Context\Context;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\SnippetTransformation\PricesJsonSnippetTransformation
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
     * @param mixed $expected
     * @param mixed $input
     */
    private function assertTransformation($expected, $input)
    {
        $result = call_user_func($this->pricesJsonSnippetTransformation, $input, $this->stubContext);
        $this->assertSame($expected, $result);
    }

    protected function setUp()
    {
        $this->stubContext = $this->getMock(Context::class);
        $this->stubPriceSnippetTransformation = $this->getMock(SnippetTransformation::class);
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
